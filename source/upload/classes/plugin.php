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
 * Protected upload source.
 *
 * @package videosummarysource_upload
 * @copyright 2026 Eduardo Kraus
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace videosummarysource_upload;

use context_module;
use mod_videosummary\source\plugin_base;
use MoodleQuickForm;
use stdClass;

/**
 * Class plugin.
 */
class plugin extends plugin_base {
    /**
     * Method get_sort_order.
     *
     * @return int Return value.
     */
    public function get_sort_order(): int {
        return 10;
    }

    /**
     * Method get_name.
     *
     * @return string Return value.
     */
    public function get_name(): string {
        return get_string('pluginname', 'videosummarysource_upload');
    }

    /**
     * Method add_form_elements.
     *
     * @param MoodleQuickForm $mform Parameter mform.
     * @param string $sourcefield Parameter sourcefield.
     * @return void Return value.
     */
    public function add_form_elements(MoodleQuickForm $mform, string $sourcefield): void {
        $options = ['subdirs' => 0, 'maxfiles' => 1, 'accepted_types' => ['.mp4', '.webm', '.ogv', '.m4v', '.mov']];
        $mform->addElement('filemanager', 'videofile', get_string('videofile', 'videosummarysource_upload'), null, $options);
        $mform->hideIf('videofile', $sourcefield, 'neq', 'upload');
    }

    /**
     * Method validation.
     *
     * @param array $data Parameter data.
     * @param array $files Parameter files.
     * @return array Return value.
     */
    public function validation(array $data, array $files): array {
        if (($data['videosource'] ?? '') !== 'upload') {
            return [];
        }
        $draftid = (int)($data['videofile'] ?? 0);
        $info = $draftid > 0 ? file_get_draft_area_info($draftid) : ['filecount' => 0];
        if (empty($info['filecount'])) {
            return ['videofile' => get_string('required')];
        }
        return [];
    }

    /**
     * Method build_config.
     *
     * @param stdClass $data Parameter data.
     * @return array Return value.
     */
    public function build_config(stdClass $data): array {
        return ['uploaded' => true];
    }

    /**
     * Method get_legacy_value.
     *
     * @param array $config Parameter config.
     * @return string Return value.
     */
    public function get_legacy_value(array $config): string {
        return 'upload';
    }

    /**
     * Method prepare_form_data.
     *
     * @param array $defaultvalues Parameter defaultvalues.
     * @param context_module $context Parameter context.
     * @return void Return value.
     */
    public function prepare_form_data(array &$defaultvalues, context_module $context): void {
        $draftid = file_get_submitted_draft_itemid('videofile');
        file_prepare_draft_area($draftid, $context->id, 'mod_videosummary', 'video', 0, [
            'subdirs' => 0, 'maxfiles' => 1, 'accepted_types' => ['.mp4', '.webm', '.ogv', '.m4v', '.mov'],
        ]);
        $defaultvalues['videofile'] = $draftid;
    }

    /**
     * Method save_files.
     *
     * @param stdClass $data Parameter data.
     * @param context_module $context Parameter context.
     * @return void Return value.
     */
    public function save_files(stdClass $data, context_module $context): void {
        if (!empty($data->videofile)) {
            file_save_draft_area_files($data->videofile, $context->id, 'mod_videosummary', 'video', 0, [
                'subdirs' => 0, 'maxfiles' => 1, 'accepted_types' => ['.mp4', '.webm', '.ogv', '.m4v', '.mov'],
            ]);
        }
    }

    /**
     * Method delete_files.
     *
     * @param context_module $context Parameter context.
     * @return void Return value.
     */
    public function delete_files(context_module $context): void {
        get_file_storage()->delete_area_files($context->id, 'mod_videosummary', 'video');
    }

    /**
     * Method get_player_config.
     *
     * @param stdClass $activity Parameter activity.
     * @param context_module $context Parameter context.
     * @return array Return value.
     */
    public function get_player_config(stdClass $activity, context_module $context): array {
        return ['type' => 'html5', 'url' => $this->first_file_url($context, 'video')];
    }

    /**
     * Method get_player_template.
     *
     * @return string Return value.
     */
    public function get_player_template(): string {
        return 'videosummarysource_upload/player';
    }

    /**
     * Method get_legacy_config.
     *
     * @param string $legacyvalue Parameter legacyvalue.
     * @return array Return value.
     */
    protected function get_legacy_config(string $legacyvalue): array {
        return ['uploaded' => true];
    }
}
