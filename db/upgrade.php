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
 * Upgrade steps of block_fredkiflexagent.
 *
 * @package    block_fredkiflexagent
 * @copyright  2026 lernsteine.com
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade the plugin.
 *
 * @param int $oldversion Version before the upgrade.
 * @return bool
 */
function xmldb_block_fredkiflexagent_upgrade($oldversion) {
    if ($oldversion < 2026102800) {
        // Earlier versions expected the course field to be created manually.
        \block_fredkiflexagent\local\access_helper::create_orgunit_field();
        upgrade_block_savepoint(true, 2026102800, 'fredkiflexagent', false);
    }
    return true;
}
