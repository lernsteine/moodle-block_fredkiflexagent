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
 * Block that lets teachers select a FredKI agent for their course.
 *
 * @package    block_fredkiflexagent
 * @copyright  2026 lernsteine.com
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_fredkiflexagent\local\access_helper;

/**
 * FredKI Flex Agent block.
 *
 * @package    block_fredkiflexagent
 * @copyright  2026 lernsteine.com
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_fredkiflexagent extends block_base {
    /**
     * Initialise the block.
     */
    public function init(): void {
        $this->title = get_string('pluginname', 'block_fredkiflexagent');
    }

    /**
     * Pages the block can be added to.
     *
     * @return array
     */
    public function applicable_formats(): array {
        return ['course-view' => true, 'site-index' => false, 'my' => false, 'mod' => false, 'all' => false];
    }

    /**
     * Only one block per course.
     *
     * @return bool
     */
    public function instance_allow_multiple(): bool {
        return false;
    }

    /**
     * The block has no global settings.
     *
     * @return bool
     */
    public function has_config(): bool {
        return false;
    }

    /**
     * The chat button floats over the page, the block itself has no header.
     *
     * @return bool
     */
    public function hide_header(): bool {
        return true;
    }

    /**
     * Block content.
     *
     * @return stdClass|null
     */
    public function get_content(): ?stdClass {
        if ($this->content !== null) {
            return $this->content;
        }
        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        $canconfigure = $this->user_can_configure();
        $selection = access_helper::get_selection($this->config ?? null);
        if ($selection === '') {
            if ($canconfigure) {
                $this->content->text = $this->render_state(
                    get_string('notconfiguredchatbot', 'block_fredkiflexagent'),
                    get_string('selectchatbotbutton', 'block_fredkiflexagent'),
                    ''
                );
            }
            return $this->content;
        }

        try {
            $choice = access_helper::get_choice((int)$this->page->course->id, $selection);
        } catch (moodle_exception $e) {
            if ($canconfigure) {
                $this->content->text = html_writer::div(
                    get_string('permissioncheckfailed', 'block_fredkiflexagent'),
                    'alert alert-warning'
                );
            }
            return $this->content;
        }

        if (!$choice) {
            if ($canconfigure) {
                $this->content->text = $this->render_state(
                    get_string('configuredchatbotnotallowed', 'block_fredkiflexagent'),
                    get_string('selectanotherchatbot', 'block_fredkiflexagent'),
                    'alert alert-warning'
                );
            }
            return $this->content;
        }

        if ($choice['type'] === 'bot') {
            $this->page->requires->js(new moodle_url('/local/fredkichat/launcher.php', [
                'bot' => (int)$choice['botid'],
                'block' => (int)$this->instance->id,
            ]));
            $caption = get_string('labeldesigned', 'block_fredkiflexagent');
        } else {
            $this->page->requires->js(new moodle_url($choice['embedurl']));
            $caption = get_string('labeloriginal', 'block_fredkiflexagent');
        }

        $html = html_writer::div('✓ ' . get_string('chatbotready', 'block_fredkiflexagent'), 'fredkiflexagent-readyline') .
            html_writer::div(format_string($choice['name']), 'fredkiflexagent-selectedname') .
            html_writer::div($caption, 'fredkiflexagent-selectedtype');
        if ($canconfigure) {
            $html .= html_writer::div(html_writer::link(
                $this->get_select_url(),
                get_string('selectanotherchatbot', 'block_fredkiflexagent')
            ), 'fredkiflexagent-actions');
        }
        $classes = 'fredkiflexagent-card fredkiflexagent-card-active fredkiflexagent-card-compact';
        $this->content->text = html_writer::div($html, $classes);
        return $this->content;
    }

    /**
     * Render the empty or warning state with a button to the selection page.
     *
     * @param string $message Message.
     * @param string $buttonlabel Button label.
     * @param string $messageclass CSS class of the message.
     * @return string
     */
    protected function render_state(string $message, string $buttonlabel, string $messageclass): string {
        $attributes = ['class' => 'btn btn-primary fredkiflexagent-primarybtn'];
        $button = html_writer::link($this->get_select_url(), $buttonlabel, $attributes);
        return html_writer::div(
            html_writer::div($message, $messageclass !== '' ? $messageclass : 'fredkiflexagent-emptytext') .
            html_writer::div($button, 'fredkiflexagent-actions'),
            'fredkiflexagent-card fredkiflexagent-card-empty'
        );
    }

    /**
     * URL of the selection page.
     *
     * @return moodle_url
     */
    protected function get_select_url(): moodle_url {
        return new moodle_url('/blocks/fredkiflexagent/select.php', ['blockid' => (int)$this->instance->id]);
    }

    /**
     * Whether the current user may select the chatbot of this block.
     *
     * @return bool
     */
    protected function user_can_configure(): bool {
        return !empty($this->context) && has_capability('block/fredkiflexagent:configureagent', $this->context);
    }
}
