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
 * plugin.php
 *
 * @package   videosummarysource_vimeo
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace videosummarysource_vimeo;

use context_module;
use mod_videosummary\source\plugin_base;
use moodle_exception;
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
        return 40;
    }

    /**
     * Method get_name.
     *
     * @return string Return value.
     */
    public function get_name(): string {
        return get_string('pluginname', 'videosummarysource_vimeo');
    }

    /**
     * Method add_form_elements.
     *
     * @param MoodleQuickForm $mform Parameter mform.
     * @param string $sourcefield Parameter sourcefield.
     * @return void Return value.
     */
    public function add_form_elements(MoodleQuickForm $mform, string $sourcefield): void {
        $mform->addElement('url', 'vimeourl',
            get_string('videourl', 'videosummarysource_vimeo'),
            ['size' => 80], ['usefilepicker' => false]);
        $mform->setType('vimeourl', PARAM_URL);
        $mform->hideIf('vimeourl', $sourcefield, 'neq', 'vimeo');
    }

    /**
     * Method validation.
     *
     * @param array $data Parameter data.
     * @param array $files Parameter files.
     * @return array Return value.
     */
    public function validation(array $data, array $files): array {
        if (($data['videosource'] ?? '') !== 'vimeo') {
            return [];
        }
        try {
            $this->build_config((object)$data);
            return [];
        } catch (moodle_exception $e) {
            return ['vimeourl' => $e->getMessage()];
        }
    }

    /**
     * Method build_config.
     *
     * @param stdClass $data Parameter data.
     * @return array Return value.
     */
    public function build_config(stdClass $data): array {
        return self::extract_config(trim((string)($data->vimeourl ?? '')));
    }

    /**
     * Method get_legacy_value.
     *
     * @param array $config Parameter config.
     * @return string Return value.
     */
    public function get_legacy_value(array $config): string {
        return (string)($config['id'] ?? '') . (!empty($config['hash']) ? ':' . $config['hash'] : '');
    }

    /**
     * Method prepare_form_data.
     *
     * @param array $defaultvalues Parameter defaultvalues.
     * @param context_module $context Parameter context.
     * @return void Return value.
     */
    public function prepare_form_data(array &$defaultvalues, context_module $context): void {
        $config = $this->decode_config((object)$defaultvalues);
        $defaultvalues['vimeourl'] = !empty($config['id']) ? 'https://vimeo.com/' .
            $config['id'] . (!empty($config['hash']) ? '/' . $config['hash'] : '') : '';
    }

    /**
     * Method get_player_config.
     *
     * @param stdClass $activity Parameter activity.
     * @param context_module $context Parameter context.
     * @return array Return value.
     */
    public function get_player_config(stdClass $activity, context_module $context): array {
        $config = $this->decode_config($activity);
        return ['type' => 'vimeo', 'vimeoid' => (string)($config['id'] ?? ''), 'vimeohash' => (string)($config['hash'] ?? '')];
    }

    /**
     * Method get_player_template.
     *
     * @return string Return value.
     */
    public function get_player_template(): string {
        return 'videosummarysource_vimeo/player';
    }

    /**
     * Method supports_poster.
     *
     * @return bool Return value.
     */
    public function supports_poster(): bool {
        return false;
    }

    /**
     * Method get_legacy_config.
     *
     * @param string $legacyvalue Parameter legacyvalue.
     * @return array Return value.
     */
    protected function get_legacy_config(string $legacyvalue): array {
        if (preg_match('/^(\d+)(?::([A-Za-z0-9]+))?$/', $legacyvalue, $m)) {
            return ['id' => $m[1], 'hash' => $m[2] ?? ''];
        }
        return self::extract_config($legacyvalue);
    }

    /**
     * Method extract_config.
     *
     * @param string $url Parameter url.
     * @return array Return value.
     */
    private static function extract_config(string $url): array {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new moodle_exception('invalidurl', 'videosummarysource_vimeo');
        }
        $host = strtolower((string)parse_url($url, PHP_URL_HOST));
        if (!in_array($host, ['vimeo.com', 'www.vimeo.com', 'player.vimeo.com'], true)) {
            throw new moodle_exception('invalidurl', 'videosummarysource_vimeo');
        }
        $path = trim((string)parse_url($url, PHP_URL_PATH), '/');
        if (!preg_match('~^(?:video/)?(\d+)(?:/([A-Za-z0-9]+))?$~', $path, $m)) {
            throw new moodle_exception('invalidurl', 'videosummarysource_vimeo');
        }
        parse_str((string)parse_url($url, PHP_URL_QUERY), $query);
        $hash = $m[2] ?? '';
        if ($hash === '' && !empty($query['h']) && preg_match('/^[A-Za-z0-9]+$/', $query['h'])) {
            $hash = $query['h'];
        }
        return ['id' => $m[1], 'hash' => $hash];
    }
}
