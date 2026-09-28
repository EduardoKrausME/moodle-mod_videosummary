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
 * videosummarysource_url.php
 *
 * @package   videosummarysource_url
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
$string['invalidurl'] = 'Enter a valid HTTP or HTTPS video URL.';
$string['pluginname'] = 'Direct URL';
$string['privacy:metadata'] = 'The direct URL source does not store personal data itself; loading a remote video may expose connection metadata to that service.';
$string['privacy:metadata:remote_video'] = 'When a direct video URL is displayed, the learner\'s browser connects to the configured remote video server.';
$string['privacy:metadata:remote_video:ipaddress'] = 'The learner\'s IP address is exposed to the remote video server as part of the browser connection.';
$string['privacy:metadata:remote_video:useragent'] = 'The learner\'s browser user-agent may be sent to the remote video server when loading the video.';
$string['videourl'] = 'Direct video URL';
