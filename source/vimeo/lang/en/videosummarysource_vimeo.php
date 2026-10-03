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
 * videosummarysource_vimeo.php
 *
 * @package   videosummarysource_vimeo
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
$string['invalidurl'] = 'Enter a valid Vimeo URL.';
$string['pluginname'] = 'Vimeo';
$string['privacy:metadata'] = 'The Vimeo source does not store personal data itself; loading the embedded player exposes connection metadata to Vimeo.';
$string['privacy:metadata:vimeo'] = 'When a Vimeo video is displayed, the learner\'s browser connects to Vimeo to load and play the video.';
$string['privacy:metadata:vimeo:ipaddress'] = 'The learner\'s IP address is exposed to Vimeo as part of the browser connection.';
$string['privacy:metadata:vimeo:useragent'] = 'The learner\'s browser user-agent may be sent to Vimeo when loading the embedded player.';
$string['videourl'] = 'Vimeo URL';
