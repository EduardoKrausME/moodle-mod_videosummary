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
 * videosummarysource_youtube.php
 *
 * @package   videosummarysource_youtube
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
$string['invalidurl'] = 'Enter a valid YouTube URL.';
$string['pluginname'] = 'YouTube';
$string['privacy:metadata'] = 'The YouTube source does not store personal data itself; loading the embedded player exposes connection metadata to YouTube.';
$string['privacy:metadata:youtube'] = 'When a YouTube video is displayed, the learner\'s browser connects to YouTube to load and play the video.';
$string['privacy:metadata:youtube:ipaddress'] = 'The learner\'s IP address is exposed to YouTube as part of the browser connection.';
$string['privacy:metadata:youtube:useragent'] = 'The learner\'s browser user-agent may be sent to YouTube when loading the embedded player.';
$string['videourl'] = 'YouTube URL';
