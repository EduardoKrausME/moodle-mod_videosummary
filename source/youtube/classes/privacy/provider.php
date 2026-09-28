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
 * Privacy provider for the YouTube video source.
 *
 * @package   videosummarysource_youtube
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace videosummarysource_youtube\privacy;

use core_privacy\local\metadata\collection;

/**
 * Privacy provider for the YouTube video source.
 */
class provider implements \core_privacy\local\metadata\provider {
    /**
     * Describes data exposed to YouTube by the learner's browser.
     *
     * @param collection $collection Metadata collection.
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_external_location_link(
            'youtube',
            [
                'ipaddress' => 'privacy:metadata:youtube:ipaddress',
                'useragent' => 'privacy:metadata:youtube:useragent',
            ],
            'privacy:metadata:youtube'
        );

        return $collection;
    }
}
