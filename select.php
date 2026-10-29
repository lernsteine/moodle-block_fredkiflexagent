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
 * Select the FredKI agent of a course block.
 *
 * @package    block_fredkiflexagent
 * @copyright  2026 lernsteine.com
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/blocklib.php');

use block_fredkiflexagent\local\access_helper;

$blockid = required_param('blockid', PARAM_INT);

$instance = $DB->get_record('block_instances', ['id' => $blockid, 'blockname' => 'fredkiflexagent'], '*', MUST_EXIST);
$blockcontext = context_block::instance($blockid);
$coursecontext = $blockcontext->get_course_context();
$course = get_course($coursecontext->instanceid);
require_login($course);
require_capability('block/fredkiflexagent:configureagent', $blockcontext);

$pageurl = new moodle_url('/blocks/fredkiflexagent/select.php', ['blockid' => $blockid]);
$courseurl = new moodle_url('/course/view.php', ['id' => $course->id]);
$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('incourse');
$PAGE->set_title(get_string('selectchatbotpagetitle', 'block_fredkiflexagent'));
$PAGE->set_heading(format_string($course->fullname));

$block = block_instance('fredkiflexagent', $instance);
$current = access_helper::get_selection($block->config ?? null);
$orgunit = access_helper::get_course_orgunit((int)$course->id);

if (optional_param('savechatbot', 0, PARAM_BOOL) && confirm_sesskey()) {
    $selected = required_param('selection', PARAM_RAW_TRIMMED);
    if (!access_helper::get_choice((int)$course->id, $selected)) {
        throw new moodle_exception('chatbotnotallowed', 'block_fredkiflexagent');
    }
    $config = $block->config ?? new stdClass();
    $config->selection = $selected;
    unset($config->botid);
    $block->instance_config_save($config);
    redirect($courseurl, get_string('chatbotsaved', 'block_fredkiflexagent'), null, \core\output\notification::NOTIFY_SUCCESS);
}

$error = '';
$choices = [];
if ($orgunit === '') {
    $error = get_string('missingorgunitfield', 'block_fredkiflexagent');
} else {
    try {
        // Always show the current state of FredKI on this page.
        $choices = access_helper::get_allowed_choices((int)$course->id, true);
    } catch (moodle_exception $e) {
        $error = $e->getMessage();
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('selectchatbotpagetitle', 'block_fredkiflexagent'), 2);
if ($orgunit !== '') {
    echo html_writer::div(get_string('orgunit', 'block_fredkiflexagent') . ': ' . s($orgunit), 'alert alert-info');
}
if (has_capability('moodle/site:config', context_system::instance())) {
    $diagurl = new moodle_url('/blocks/fredkiflexagent/diagnostic.php', ['courseid' => $course->id]);
    echo html_writer::div(html_writer::link(
        $diagurl,
        get_string('opendiagnostic', 'block_fredkiflexagent'),
        ['class' => 'btn btn-outline-secondary btn-sm']
    ), 'mb-3');
}

if ($error !== '') {
    echo $OUTPUT->notification($error, 'warning');
} else if (!$choices) {
    echo $OUTPUT->notification(get_string('noagents_orgunit', 'block_fredkiflexagent'), 'warning');
} else {
    echo html_writer::start_tag('form', ['method' => 'post', 'action' => $pageurl->out(false)]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'savechatbot', 'value' => '1']);
    echo html_writer::start_div('fredkiflexagent-agentgrid');
    $badges = [
        'designed' => get_string('badgeindividual', 'block_fredkiflexagent'),
        'standard' => get_string('badgestandard', 'block_fredkiflexagent'),
        'unavailable' => get_string('badgeunavailable', 'block_fredkiflexagent'),
    ];
    foreach ($choices as $choice) {
        $status = $choice['status'];
        $available = !empty($choice['available']);
        $checked = $available && $choice['key'] === $current;
        $id = 'fredkichoice-' . substr(sha1($choice['key']), 0, 12);

        $radioattrs = ['type' => 'radio', 'name' => 'selection', 'value' => $choice['key'], 'id' => $id];
        if ($checked) {
            $radioattrs['checked'] = 'checked';
        }
        if (!$available) {
            $radioattrs['disabled'] = 'disabled';
        }

        $classes = 'fredkiflexagent-agentcard fredkiflexagent-card-' . $status;
        if ($checked) {
            $classes .= ' is-selected';
        }
        if (!$available) {
            $classes .= ' is-disabled';
        }

        $cardcontent = html_writer::div(
            html_writer::div(format_string($choice['name']), 'fredkiflexagent-agentname') .
            html_writer::span($badges[$status], 'fredkiflexagent-statusbadge fredkiflexagent-status-' . $status),
            'fredkiflexagent-cardhead'
        );
        if (!$available) {
            $cardcontent .= html_writer::div(
                get_string('unavailablehint', 'block_fredkiflexagent'),
                'fredkiflexagent-unavailablehint'
            );
        }
        $label = html_writer::tag('label', $cardcontent, ['for' => $id, 'class' => $classes,
            'aria-disabled' => $available ? 'false' : 'true']);
        echo html_writer::div(html_writer::empty_tag('input', $radioattrs) . $label, 'fredkiflexagent-agentchoice');
    }
    echo html_writer::end_div();
    echo html_writer::div(html_writer::empty_tag('input', ['type' => 'submit', 'class' => 'btn btn-primary',
        'value' => get_string('savechatbot', 'block_fredkiflexagent')]), 'mt-3');
    echo html_writer::end_tag('form');
}
echo html_writer::div(html_writer::link($courseurl, get_string('backtocourse', 'block_fredkiflexagent')), 'mt-3');
echo $OUTPUT->footer();
