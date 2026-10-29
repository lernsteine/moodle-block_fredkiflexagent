<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace block_fredkiflexagent\local;

use local_fredkichat\admin_client;
use local_fredkichat\bot_helper;

/**
 * Resolves which FredKI agents a course may use.
 *
 * The course custom field "fredki_orgunit" names a FredKI folder. Only agents in that folder,
 * as returned to the FredKI service account, are offered for the course.
 *
 * @package    block_fredkiflexagent
 * @copyright  2026 lernsteine.com
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class access_helper {
    /** @var string Short name of the course custom field holding the FredKI folder name. */
    const ORGUNIT_FIELD = 'fredki_orgunit';

    /**
     * Return the FredKI folder name of a course.
     *
     * @param int $courseid Course id.
     * @return string
     */
    public static function get_course_orgunit(int $courseid): string {
        $data = \core_course\customfield\course_handler::create()->export_instance_data_object($courseid, true);
        $value = $data->{self::ORGUNIT_FIELD} ?? '';
        if (!is_scalar($value)) {
            return '';
        }
        return trim(html_entity_decode(strip_tags((string)$value), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    /**
     * Return the course custom field record of the organisation unit, if it exists.
     *
     * @return \stdClass|null
     */
    public static function get_orgunit_field(): ?\stdClass {
        global $DB;
        $sql = "SELECT f.*
                  FROM {customfield_field} f
                  JOIN {customfield_category} c ON c.id = f.categoryid
                 WHERE f.shortname = :shortname AND c.component = :component AND c.area = :area";
        $field = $DB->get_record_sql($sql, ['shortname' => self::ORGUNIT_FIELD, 'component' => 'core_course',
            'area' => 'course'], IGNORE_MULTIPLE);
        return $field ?: null;
    }

    /**
     * Create the locked and hidden course custom field "fredki_orgunit" unless it exists.
     *
     * Only users who may change locked course fields (by default managers and administrators)
     * decide which FredKI folder a course may use.
     */
    public static function create_orgunit_field(): void {
        if (self::get_orgunit_field()) {
            return;
        }
        $handler = \core_course\customfield\course_handler::create();
        $categoryid = $handler->create_category(get_string('customfieldcategory', 'block_fredkiflexagent'));
        $category = \core_customfield\category_controller::create($categoryid);
        $field = \core_customfield\field_controller::create(0, (object)['type' => 'text'], $category);
        \core_customfield\api::save_field_configuration($field, (object)[
            'name' => get_string('orgunit', 'block_fredkiflexagent'),
            'shortname' => self::ORGUNIT_FIELD,
            'description' => get_string('orgunit_desc', 'block_fredkiflexagent'),
            'descriptionformat' => FORMAT_HTML,
            'configdata' => [
                'required' => 0,
                'uniquevalues' => 0,
                'locked' => 1,
                'visibility' => 0,
                'defaultvalue' => '',
                'displaysize' => 50,
                'maxlength' => 255,
                'ispassword' => 0,
                'link' => '',
                'linktarget' => '',
            ],
        ]);
    }

    /**
     * Whether teachers are prevented from changing the organisation unit field.
     *
     * @return bool
     */
    public static function orgunit_field_is_locked(): bool {
        $field = self::get_orgunit_field();
        if (!$field) {
            return false;
        }
        $config = json_decode((string)$field->configdata, true);
        return !empty($config['locked']);
    }

    /**
     * Return the choices of a course: agents with a Moodle design first, then plain FredKI agents.
     *
     * Results are cached per organisation unit for a few minutes, so viewing a course does not
     * contact FredKI on every page load.
     *
     * @param int $courseid Course id.
     * @param bool $refresh Ignore the cache.
     * @return array[]
     */
    public static function get_allowed_choices(int $courseid, bool $refresh = false): array {
        $orgunit = self::get_course_orgunit($courseid);
        if ($orgunit === '') {
            return [];
        }
        $cache = \cache::make('block_fredkiflexagent', 'choices');
        $key = sha1(\core_text::strtolower($orgunit));
        if (!$refresh) {
            $cached = $cache->get($key);
            if (is_array($cached)) {
                return $cached;
            }
        }
        $choices = self::load_choices($orgunit);
        $cache->set($key, $choices);
        return $choices;
    }

    /**
     * Load the choices of an organisation unit from FredKI.
     *
     * @param string $orgunit FredKI folder name.
     * @return array[]
     */
    private static function load_choices(string $orgunit): array {
        $client = admin_client::from_config();
        $folderid = null;
        foreach ($client->list_folders() as $folder) {
            if (self::normalise($folder['name']) === self::normalise($orgunit)) {
                $folderid = $folder['id'];
                break;
            }
        }
        // Without a matching folder no agent is offered. There is no fallback to the global list.
        if ($folderid === null) {
            return [];
        }

        $bots = bot_helper::get_bots_by_agentid();
        $choices = [];
        foreach ($client->list_agents_in_folder($folderid) as $agent) {
            if (isset($bots[$agent['id']])) {
                $bot = $bots[$agent['id']];
                $choices[] = [
                    'key' => 'bot:' . (int)$bot->id,
                    'type' => 'bot',
                    'botid' => (int)$bot->id,
                    'agentid' => $agent['id'],
                    'name' => (string)$bot->name,
                    'status' => 'designed',
                    'available' => true,
                    'embedurl' => '',
                ];
                continue;
            }
            $embedurl = '';
            try {
                $access = $client->get_access_token($agent['id']);
                if ($access['token'] !== '' && $access['active']) {
                    $embedurl = self::build_embed_url($client->get_baseurl(), $access['token']);
                }
            } catch (\moodle_exception $e) {
                $embedurl = '';
            }
            $choices[] = [
                'key' => 'agent:' . $agent['id'],
                'type' => 'agent',
                'botid' => 0,
                'agentid' => $agent['id'],
                'name' => $agent['name'],
                'status' => $embedurl !== '' ? 'standard' : 'unavailable',
                'available' => $embedurl !== '',
                'embedurl' => $embedurl,
            ];
        }

        $order = ['designed' => 0, 'standard' => 1, 'unavailable' => 2];
        usort($choices, static function (array $a, array $b) use ($order): int {
            return [$order[$a['status']], \core_text::strtolower($a['name'])] <=>
                [$order[$b['status']], \core_text::strtolower($b['name'])];
        });
        return $choices;
    }

    /**
     * Return one available choice of a course.
     *
     * @param int $courseid Course id.
     * @param string $key Choice key ("bot:<id>" or "agent:<id>").
     * @param bool $refresh Ignore the cache.
     * @return array|null
     */
    public static function get_choice(int $courseid, string $key, bool $refresh = false): ?array {
        foreach (self::get_allowed_choices($courseid, $refresh) as $choice) {
            if ($choice['key'] === $key && !empty($choice['available'])) {
                return $choice;
            }
        }
        return null;
    }

    /**
     * Read the selection stored in a block instance, including the legacy "botid" setting.
     *
     * @param \stdClass|null $config Block configuration.
     * @return string
     */
    public static function get_selection(?\stdClass $config): string {
        $selection = trim((string)($config->selection ?? ''));
        if ($selection === '' && !empty($config->botid)) {
            $selection = 'bot:' . (int)$config->botid;
        }
        return $selection;
    }

    /**
     * URL of the original FredKI embed script for an agent.
     *
     * @param string $baseurl FredKI base URL.
     * @param string $token Public access token.
     * @return string
     */
    private static function build_embed_url(string $baseurl, string $token): string {
        $host = parse_url($baseurl, PHP_URL_HOST);
        $scheme = parse_url($baseurl, PHP_URL_SCHEME) ?: 'https';
        if (!$host) {
            return '';
        }
        $port = parse_url($baseurl, PHP_URL_PORT);
        return $baseurl . '/chat/api/embed?' . http_build_query([
            'protocol' => $scheme,
            'host' => $host . ($port ? ':' . $port : ''),
            'token' => $token,
        ], '', '&');
    }

    /**
     * Normalise a folder name for comparison.
     *
     * @param string $value Name.
     * @return string
     */
    private static function normalise(string $value): string {
        return (string)preg_replace('/\s+/u', ' ', trim(\core_text::strtolower($value)));
    }
}
