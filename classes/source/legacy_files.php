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
 * Legacy uploaded-video compatibility for Video Bridge.
 *
 * @package   mod_videosummary
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videosummary\source;

use context_module;

/**
 * Copies existing protected video uploads into the shared source file area.
 */
final class legacy_files {
    /**
     * Idempotently migrate an old Video Summary upload, without deleting its original.
     *
     * @param context_module $context The activity context.
     * @return void
     */
    public static function migrate(context_module $context): void {
        $storage = get_file_storage();
        if ($storage->get_area_files($context->id, 'local_video_bridge', 'video', 0, 'id', false)) {
            return;
        }

        $legacyfiles = $storage->get_area_files($context->id, 'mod_videosummary', 'video', 0, 'id', false);
        foreach ($legacyfiles as $file) {
            $filerecord = [
                'contextid' => $context->id,
                'component' => 'local_video_bridge',
                'filearea' => 'video',
                'itemid' => 0,
                'filepath' => $file->get_filepath(),
                'filename' => $file->get_filename(),
                'userid' => $file->get_userid(),
                'author' => $file->get_author(),
                'license' => $file->get_license(),
                'source' => $file->get_source(),
            ];
            if (!$storage->file_exists(
                $context->id,
                'local_video_bridge',
                'video',
                0,
                $file->get_filepath(),
                $file->get_filename()
            )) {
                $storage->create_file_from_storedfile($filerecord, $file);
            }
        }
    }
}
