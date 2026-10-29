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
 * FredKI diagnostics for administrators.
 *
 * Shows how the organisation unit of a course is resolved and which agents FredKI returns.
 * Credentials and tokens are never shown.
 *
 * @package    block_fredkiflexagent
 * @copyright  2026 lernsteine.com
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

use block_fredkiflexagent\local\access_helper;
use local_fredkichat\admin_client;

$courseid = required_param('courseid', PARAM_INT);
$course = get_course($courseid);
require_login($course);
require_capability('moodle/site:config', context_system::instance());

$PAGE->set_url(new moodle_url('/blocks/fredkiflexagent/diagnostic.php', ['courseid' => $courseid]));
$PAGE->set_pagelayout('incourse');
$PAGE->set_title(get_string('diagnostictitle', 'block_fredkiflexagent'));
$PAGE->set_heading(format_string($course->fullname));

$yesno = static fn(bool $value): string => get_string($value ? 'yes' : 'no');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('diagnostictitle', 'block_fredkiflexagent'), 2);
echo $OUTPUT->notification(get_string('diagnosticintro', 'block_fredkiflexagent'), 'info', false);

// Course field.
echo html_writer::tag('h3', get_string('diagnosticfield', 'block_fredkiflexagent'));
$field = access_helper::get_orgunit_field();
$orgunit = access_helper::get_course_orgunit($courseid);
$table = new html_table();
$table->data = [
    [get_string('diagnosticfieldexists', 'block_fredkiflexagent'), $yesno((bool)$field)],
    [get_string('diagnosticfieldlocked', 'block_fredkiflexagent'), $yesno(access_helper::orgunit_field_is_locked())],
    [get_string('orgunit', 'block_fredkiflexagent'), $orgunit !== '' ? s($orgunit) : '—'],
];
echo html_writer::table($table);
if ($field && !access_helper::orgunit_field_is_locked()) {
    echo $OUTPUT->notification(get_string('diagnosticfieldnotlocked', 'block_fredkiflexagent'), 'warning', false);
}

try {
    $client = admin_client::from_config();

    // Folder resolution.
    echo html_writer::tag('h3', get_string('diagnosticfolder', 'block_fredkiflexagent'));
    $folders = $client->list_folders();
    $table = new html_table();
    $table->head = [get_string('diagnosticfoldername', 'block_fredkiflexagent'), 'ID',
        get_string('diagnosticmatch', 'block_fredkiflexagent')];
    foreach ($folders as $folder) {
        $match = $orgunit !== '' && core_text::strtolower(trim($folder['name'])) === core_text::strtolower($orgunit);
        $table->data[] = [s($folder['name']), s($folder['id']), $yesno($match)];
    }
    echo html_writer::table($table);

    // Agents offered to the course.
    echo html_writer::tag('h3', get_string('diagnosticallowed', 'block_fredkiflexagent'));
    $choices = access_helper::get_allowed_choices($courseid, true);
    $table = new html_table();
    $table->head = [get_string('chatbot', 'block_fredkiflexagent'), 'ID', get_string('diagnosticstatus', 'block_fredkiflexagent')];
    foreach ($choices as $choice) {
        $table->data[] = [format_string($choice['name']), s($choice['agentid']),
            get_string('badge' . ['designed' => 'individual', 'standard' => 'standard',
                'unavailable' => 'unavailable'][$choice['status']], 'block_fredkiflexagent')];
    }
    if (!$table->data) {
        $table->data[] = [get_string('noagents_orgunit', 'block_fredkiflexagent'), '', ''];
    }
    echo html_writer::table($table);
} catch (moodle_exception $e) {
    echo $OUTPUT->notification($e->getMessage(), 'error', false);
}

echo html_writer::div(html_writer::link(
    new moodle_url('/course/view.php', ['id' => $courseid]),
    get_string('backtocourse', 'block_fredkiflexagent'),
    ['class' => 'btn btn-primary']
), 'mt-4');
echo $OUTPUT->footer();
