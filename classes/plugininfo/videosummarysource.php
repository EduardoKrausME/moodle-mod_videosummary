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
 * Plugin information class for Video Summary source subplugins.
 *
 * @package   mod_videosummary
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videosummary\plugininfo;

use core\plugininfo\base;

/**
 * Plugin information for videosummarysource subplugins.
 */
class videosummarysource extends base {
    /**
     * Allow source subplugins to be uninstalled from the plugin overview.
     *
     * @return bool
     */
    public function is_uninstall_allowed(): bool {
        return true;
    }
}
