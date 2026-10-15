<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Upgrade steps for Video Summary.
 *
 * @package   mod_videosummary
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Performs database upgrades for Video Summary.
 *
 * @param int $oldversion Previously installed version.
 * @return bool
 */
function xmldb_videosummary_upgrade(int $oldversion): bool {
    global $DB;

    if ($oldversion < 2026101500) {
        // Remove configuration belonging to the retired videosummarysource subplugins.
        // Escape the underscore to match the literal component prefix, not a SQL wildcard.
        $like = $DB->sql_like('plugin', ':sourceprefix');
        $sourceprefix = $DB->sql_like_escape('videosummarysource_') . '%';
        $DB->delete_records_select('config_plugins', $like, ['sourceprefix' => $sourceprefix]);

        upgrade_mod_savepoint(true, 2026101500, 'videosummary');
    }

    return true;
}
