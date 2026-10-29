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
 * English strings for block_fredkiflexagent.
 *
 * @package    block_fredkiflexagent
 * @category   string
 * @copyright  2026 lernsteine.com
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['backtocourse'] = 'Back to course';
$string['badgeindividual'] = 'Custom design';
$string['badgestandard'] = 'FredKI standard';
$string['badgeunavailable'] = 'Unavailable';
$string['cachedef_choices'] = 'FredKI agents per organisation unit';
$string['chatbot'] = 'Chatbot';
$string['chatbotnotallowed'] = 'This chatbot is not available for the organisation unit of this course.';
$string['chatbotready'] = 'FredKI is ready for this course.';
$string['chatbotsaved'] = 'The FredKI chatbot was saved for this course.';
$string['configuredchatbotnotallowed'] = 'The selected chatbot is no longer available for the organisation unit of this course. Please select another chatbot.';
$string['customfieldcategory'] = 'FredKI';
$string['diagnosticallowed'] = 'Agents offered to this course';
$string['diagnosticfield'] = 'Course field';
$string['diagnosticfieldexists'] = 'Course field “fredki_orgunit” exists';
$string['diagnosticfieldlocked'] = 'Course field is locked for teachers';
$string['diagnosticfieldnotlocked'] = 'The course field “fredki_orgunit” is not locked. Teachers could change it and select agents of other organisation units. Lock the field in Site administration > Courses > Course custom fields.';
$string['diagnosticfolder'] = 'FredKI folders';
$string['diagnosticfoldername'] = 'Folder';
$string['diagnosticintro'] = 'This page shows how the organisation unit of this course is resolved in FredKI. Credentials and tokens are never shown.';
$string['diagnosticmatch'] = 'Matches the course';
$string['diagnosticstatus'] = 'Status';
$string['diagnostictitle'] = 'FredKI diagnostics';
$string['fredkiflexagent:addinstance'] = 'Add a new FredKI Flex Agent block';
$string['fredkiflexagent:configureagent'] = 'Select the FredKI chatbot of the block';
$string['labeldesigned'] = 'With Moodle design';
$string['labeloriginal'] = 'FredKI original';
$string['missingorgunitfield'] = 'No FredKI organisation unit is set for this course. An administrator must fill in the course field “FredKI organisation unit”.';
$string['noagents_orgunit'] = 'No FredKI agents were found for the organisation unit of this course.';
$string['notconfiguredchatbot'] = 'No FredKI chatbot has been selected for this course yet.';
$string['opendiagnostic'] = 'FredKI diagnostics';
$string['orgunit'] = 'FredKI organisation unit';
$string['orgunit_desc'] = 'Name of the FredKI folder whose agents this course may use. Set by administrators.';
$string['permissioncheckfailed'] = 'FredKI could not be reached to check the chatbot. The chatbot is not shown until the check succeeds.';
$string['pluginname'] = 'FredKI Flex Agent';
$string['privacy:metadata:fredki'] = 'For FredKI agents without a Moodle design the original FredKI chat is embedded. Messages are then sent directly from the browser to FredKI. No Moodle user identity is transmitted.';
$string['privacy:metadata:fredki:message'] = 'The message the user types into the chat.';
$string['savechatbot'] = 'Save chatbot';
$string['selectanotherchatbot'] = 'Select another chatbot';
$string['selectchatbotbutton'] = 'Select chatbot';
$string['selectchatbotpagetitle'] = 'Select FredKI chatbot';
$string['unavailablehint'] = 'The agent exists in FredKI, but its public access is not enabled.';
