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
 * @package   videosummarysource_youtube
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace videosummarysource_youtube;

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
        return 30;
    }

    /**
     * Method get_name.
     *
     * @return string Return value.
     */
    public function get_name(): string {
        return get_string('pluginname', 'videosummarysource_youtube');
    }

    /**
     * Method add_form_elements.
     *
     * @param MoodleQuickForm $mform Parameter mform.
     * @param string $sourcefield Parameter sourcefield.
     * @return void Return value.
     */
    public function add_form_elements(MoodleQuickForm $mform, string $sourcefield): void {
        $mform->addElement('url', 'youtubeurl',
            get_string('videourl', 'videosummarysource_youtube'),
            ['size' => 80], ['usefilepicker' => false]);
        $mform->setType('youtubeurl', PARAM_URL);
        $mform->hideIf('youtubeurl', $sourcefield, 'neq', 'youtube');
    }

    /**
     * Method validation.
     *
     * @param array $data Parameter data.
     * @param array $files Parameter files.
     * @return array Return value.
     */
    public function validation(array $data, array $files): array {
        if (($data['videosource'] ?? '') !== 'youtube') {
            return [];
        }
        try {
            $this->build_config((object)$data);
            return [];
        } catch (moodle_exception $e) {
            return ['youtubeurl' => $e->getMessage()];
        }
    }

    /**
     * Method build_config.
     *
     * @param stdClass $data Parameter data.
     * @return array Return value.
     */
    public function build_config(stdClass $data): array {
        return ['id' => self::extract_id(trim((string)($data->youtubeurl ?? '')))];
    }

    /**
     * Method get_legacy_value.
     *
     * @param array $config Parameter config.
     * @return string Return value.
     */
    public function get_legacy_value(array $config): string {
        return (string)($config['id'] ?? '');
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
        $defaultvalues['youtubeurl'] = !empty($config['id']) ? 'https://www.youtube.com/watch?v=' . $config['id'] : '';
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
        return ['type' => 'youtube', 'youtubeid' => (string)($config['id'] ?? '')];
    }

    /**
     * Method get_player_template.
     *
     * @return string Return value.
     */
    public function get_player_template(): string {
        return 'videosummarysource_youtube/player';
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
        if (preg_match('/^[A-Za-z0-9_-]{6,20}$/', $legacyvalue)) {
            return ['id' => $legacyvalue];
        }
        return ['id' => self::extract_id($legacyvalue)];
    }

    /**
     * Method extract_id.
     *
     * @param string $url Parameter url.
     * @return string Return value.
     */
    private static function extract_id(string $url): string {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new moodle_exception('invalidurl', 'videosummarysource_youtube');
        }
        $host = strtolower((string)parse_url($url, PHP_URL_HOST));
        $path = trim((string)parse_url($url, PHP_URL_PATH), '/');
        $id = '';

        $sources = ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtube-nocookie.com', 'www.youtube-nocookie.com'];
        if (in_array($host, ['youtu.be', 'www.youtu.be'], true)) {
            $id = explode('/', $path)[0] ?? '';
        } else if (in_array($host, $sources, true)) {
            parse_str((string)parse_url($url, PHP_URL_QUERY), $query);
            $id = (string)($query['v'] ?? '');
            if ($id === '' && preg_match('~(?:embed|shorts)/([A-Za-z0-9_-]{6,20})~', $path, $m)) {
                $id = $m[1];
            }
        }
        if (!preg_match('/^[A-Za-z0-9_-]{6,20}$/', $id)) {
            throw new moodle_exception('invalidurl', 'videosummarysource_youtube');
        }
        return $id;
    }
}
