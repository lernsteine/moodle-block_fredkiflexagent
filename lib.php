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

/**
 * Library functions of block_fredkiflexagent.
 *
 * @package    block_fredkiflexagent
 * @copyright  2026 lernsteine.com
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Callback of local_fredkichat: whether this block instance has selected a styled chatbot.
 *
 * local_fredkichat checks course access and block visibility before calling this.
 *
 * @param stdClass $instance Record of the block instance.
 * @param int $botid local_fredkichat chatbot id.
 * @return bool
 */
function block_fredkiflexagent_local_fredkichat_allows_bot(stdClass $instance, int $botid): bool {
    $config = !empty($instance->configdata) ? unserialize_object(base64_decode($instance->configdata)) : null;
    $selection = \block_fredkiflexagent\local\access_helper::get_selection($config ?: null);
    if ($selection !== 'bot:' . $botid) {
        return false;
    }
    $coursecontext = context_block::instance((int)$instance->id)->get_course_context(false);
    if (!$coursecontext) {
        return false;
    }
    try {
        return \block_fredkiflexagent\local\access_helper::get_choice((int)$coursecontext->instanceid, $selection) !== null;
    } catch (moodle_exception $e) {
        return false;
    }
}
