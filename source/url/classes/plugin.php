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
 * @package   videosummarysource_url
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace videosummarysource_url;

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
        return 20;
    }

    /**
     * Method get_name.
     *
     * @return string Return value.
     */
    public function get_name(): string {
        return get_string('pluginname', 'videosummarysource_url');
    }

    /**
     * Method add_form_elements.
     *
     * @param MoodleQuickForm $mform Parameter mform.
     * @param string $sourcefield Parameter sourcefield.
     * @return void Return value.
     */
    public function add_form_elements(MoodleQuickForm $mform, string $sourcefield): void {
        $mform->addElement('url', 'directurl',
            get_string('videourl', 'videosummarysource_url'), ['size' => 80], ['usefilepicker' => false]);
        $mform->setType('directurl', PARAM_URL);
        $mform->hideIf('directurl', $sourcefield, 'neq', 'url');
    }

    /**
     * Method validation.
     *
     * @param array $data Parameter data.
     * @param array $files Parameter files.
     * @return array Return value.
     */
    public function validation(array $data, array $files): array {
        if (($data['videosource'] ?? '') !== 'url') {
            return [];
        }
        try {
            $this->build_config((object)$data);
            return [];
        } catch (moodle_exception $e) {
            return ['directurl' => $e->getMessage()];
        }
    }

    /**
     * Method build_config.
     *
     * @param stdClass $data Parameter data.
     * @return array Return value.
     */
    public function build_config(stdClass $data): array {
        $url = trim((string)($data->directurl ?? ''));
        if (!filter_var($url, FILTER_VALIDATE_URL) ||
            !in_array(strtolower((string)parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true)) {
            throw new moodle_exception('invalidurl', 'videosummarysource_url');
        }
        return ['url' => $url];
    }

    /**
     * Method get_legacy_value.
     *
     * @param array $config Parameter config.
     * @return string Return value.
     */
    public function get_legacy_value(array $config): string {
        return (string)($config['url'] ?? '');
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
        $defaultvalues['directurl'] = (string)($config['url'] ?? '');
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
        return ['type' => 'html5', 'url' => (string)($config['url'] ?? '')];
    }

    /**
     * Method get_player_template.
     *
     * @return string Return value.
     */
    public function get_player_template(): string {
        return 'videosummarysource_url/player';
    }

    /**
     * Method get_legacy_config.
     *
     * @param string $legacyvalue Parameter legacyvalue.
     * @return array Return value.
     */
    protected function get_legacy_config(string $legacyvalue): array {
        return ['url' => $legacyvalue];
    }
}
