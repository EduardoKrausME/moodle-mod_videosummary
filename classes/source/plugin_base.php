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
 * Base contract for bundled Video Summary sources.
 *
 * @package   mod_videosummary
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videosummary\source;

use context_module;
use moodle_url;
use MoodleQuickForm;
use stdClass;

/**
 * Class plugin_base.
 */
abstract class plugin_base {
    /**
     * Method get_sort_order.
     *
     * @return int Return value.
     */
    public function get_sort_order(): int {
        return 100;
    }

    /**
     * Method get_name.
     *
     * @return string Return value.
     */
    abstract public function get_name(): string;

    /**
     * Method add_form_elements.
     *
     * @param MoodleQuickForm $mform Parameter mform.
     * @param string $sourcefield Parameter sourcefield.
     * @return void Return value.
     */
    abstract public function add_form_elements(MoodleQuickForm $mform, string $sourcefield): void;

    /**
     * Method validation.
     *
     * @param array $data Parameter data.
     * @param array $files Parameter files.
     * @return array Return value.
     */
    abstract public function validation(array $data, array $files): array;

    /**
     * Method build_config.
     *
     * @param stdClass $data Parameter data.
     * @return array Return value.
     */
    abstract public function build_config(stdClass $data): array;

    /**
     * Method get_legacy_value.
     *
     * @param array $config Parameter config.
     * @return string Return value.
     */
    abstract public function get_legacy_value(array $config): string;

    /**
     * Method get_player_config.
     *
     * @param stdClass $activity Parameter activity.
     * @param context_module $context Parameter context.
     * @return array Return value.
     */
    abstract public function get_player_config(stdClass $activity, context_module $context): array;

    /**
     * Method get_player_template.
     *
     * @return string Return value.
     */
    abstract public function get_player_template(): string;

    /**
     * Method prepare_form_data.
     *
     * @param array $defaultvalues Parameter defaultvalues.
     * @param context_module $context Parameter context.
     * @return void Return value.
     */
    public function prepare_form_data(array &$defaultvalues, context_module $context): void {
    }

    /**
     * Method save_files.
     *
     * @param stdClass $data Parameter data.
     * @param context_module $context Parameter context.
     * @return void Return value.
     */
    public function save_files(stdClass $data, context_module $context): void {
    }

    /**
     * Method delete_files.
     *
     * @param context_module $context Parameter context.
     * @return void Return value.
     */
    public function delete_files(context_module $context): void {
    }

    /**
     * Method supports_poster.
     *
     * @return bool Return value.
     */
    public function supports_poster(): bool {
        return true;
    }

    /**
     * Method decode_config.
     *
     * @param stdClass $activity Parameter activity.
     * @return array Return value.
     */
    final protected function decode_config(stdClass $activity): array {
        $raw = trim((string)($activity->sourceconfig ?? ''));
        if ($raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        return $this->get_legacy_config((string)($activity->videourl ?? ''));
    }

    /**
     * Method get_legacy_config.
     *
     * @param string $legacyvalue Parameter legacyvalue.
     * @return array Return value.
     */
    abstract protected function get_legacy_config(string $legacyvalue): array;

    /**
     * Method first_file_url.
     *
     * @param context_module $context Parameter context.
     * @param string $filearea Parameter filearea.
     * @return string Return value.
     */
    final protected function first_file_url(context_module $context, string $filearea): string {
        $files = get_file_storage()->get_area_files(
            $context->id,
            'mod_videosummary',
            $filearea,
            0,
            'filename',
            false
        );
        if (!$files) {
            return '';
        }
        $file = reset($files);
        return moodle_url::make_pluginfile_url(
            $context->id,
            'mod_videosummary',
            $filearea,
            0,
            $file->get_filepath(),
            $file->get_filename()
        )->out(false);
    }
}
