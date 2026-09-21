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
 * Source subplugin discovery and delegation.
 *
 * @package   mod_videosummary
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videosummary\source;

use context_module;
use core_collator;
use core_component;
use moodle_exception;
use MoodleQuickForm;
use stdClass;

/**
 * Class manager.
 */
class manager {
    /** @var plugin_base[]|null */
    private ?array $plugins = null;

    /**
     * Method get_plugins.
     *
     * @return array Return value.
     */
    public function get_plugins(): array {
        if ($this->plugins !== null) {
            return $this->plugins;
        }
        $plugins = [];
        foreach (array_keys(core_component::get_plugin_list('videosummarysource')) as $name) {
            $class = '\\videosummarysource_' . $name . '\\plugin';
            if (class_exists($class) && is_subclass_of($class, plugin_base::class)) {
                $plugins[$name] = new $class();
            }
        }
        $groups = [];
        foreach ($plugins as $name => $plugin) {
            $groups[$plugin->get_sort_order()][$name] = $plugin->get_name();
        }
        ksort($groups, SORT_NUMERIC);
        $sorted = [];
        foreach ($groups as $names) {
            core_collator::asort($names);
            foreach (array_keys($names) as $name) {
                $sorted[$name] = $plugins[$name];
            }
        }
        $this->plugins = $sorted;
        return $this->plugins;
    }

    /**
     * Method get_plugin.
     *
     * @param string $name Parameter name.
     * @return plugin_base Return value.
     */
    public function get_plugin(string $name): plugin_base {
        $plugins = $this->get_plugins();
        if (!isset($plugins[$name])) {
            throw new moodle_exception('sourcepluginmissing', 'videosummary', '', $name);
        }
        return $plugins[$name];
    }

    /**
     * Method get_options.
     *
     * @return array Return value.
     */
    public function get_options(): array {
        $out = [];
        foreach ($this->get_plugins() as $name => $plugin) {
            $out[$name] = $plugin->get_name();
        }
        return $out;
    }

    /**
     * Method get_default_source.
     *
     * @return string Return value.
     */
    public function get_default_source(): string {
        return (string)(array_key_first($this->get_plugins()) ?? '');
    }

    /**
     * Method add_form_elements.
     *
     * @param MoodleQuickForm $mform Parameter mform.
     * @param string $sourcefield Parameter sourcefield.
     * @return void Return value.
     */
    public function add_form_elements(MoodleQuickForm $mform, string $sourcefield): void {
        foreach ($this->get_plugins() as $plugin) {
            $plugin->add_form_elements($mform, $sourcefield);
        }
    }

    /**
     * Method validation.
     *
     * @param array $data Parameter data.
     * @param array $files Parameter files.
     * @return array Return value.
     */
    public function validation(array $data, array $files): array {
        $source = clean_param((string)($data['videosource'] ?? ''), PARAM_PLUGIN);
        try {
            return $this->get_plugin($source)->validation($data, $files);
        } catch (moodle_exception $e) {
            return ['videosource' => $e->getMessage()];
        }
    }

    /**
     * Method normalise_record.
     *
     * @param stdClass $data Parameter data.
     * @return void Return value.
     */
    public function normalise_record(stdClass $data): void {
        $source = clean_param((string)$data->videosource, PARAM_PLUGIN);
        $plugin = $this->get_plugin($source);
        $config = $plugin->build_config($data);
        $data->sourceconfig = json_encode($config, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $data->videourl = $plugin->get_legacy_value($config);
    }

    /**
     * Method prepare_form_data.
     *
     * @param array $defaultvalues Parameter defaultvalues.
     * @param context_module $context Parameter context.
     * @return void Return value.
     */
    public function prepare_form_data(array &$defaultvalues, context_module $context): void {
        $source = clean_param((string)($defaultvalues['videosource'] ?? ''), PARAM_PLUGIN);
        if ($source !== '') {
            $this->get_plugin($source)->prepare_form_data($defaultvalues, $context);
        }
    }

    /**
     * Method save_files.
     *
     * @param stdClass $data Parameter data.
     * @param context_module $context Parameter context.
     * @param ?string $previoussource Parameter previoussource.
     * @return void Return value.
     */
    public function save_files(stdClass $data, context_module $context, ?string $previoussource = null): void {
        $source = clean_param((string)$data->videosource, PARAM_PLUGIN);
        if ($previoussource && $previoussource !== $source) {
            $plugins = $this->get_plugins();
            if (isset($plugins[$previoussource])) {
                $plugins[$previoussource]->delete_files($context);
            }
        }
        $this->get_plugin($source)->save_files($data, $context);
    }

    /**
     * Method delete_files.
     *
     * @param context_module $context Parameter context.
     * @return void Return value.
     */
    public function delete_files(context_module $context): void {
        foreach ($this->get_plugins() as $plugin) {
            $plugin->delete_files($context);
        }
    }

    /**
     * Method get_sources_without_poster.
     *
     * @return array Return value.
     */
    public function get_sources_without_poster(): array {
        return array_keys(array_filter(
            $this->get_plugins(),
            static fn(plugin_base $plugin): bool => !$plugin->supports_poster()
        ));
    }

    /**
     * Method get_player_config.
     *
     * @param stdClass $activity Parameter activity.
     * @param context_module $context Parameter context.
     * @return array Return value.
     */
    public function get_player_config(stdClass $activity, context_module $context): array {
        $plugin = $this->get_plugin(clean_param((string)$activity->videosource, PARAM_PLUGIN));
        return $plugin->get_player_config($activity, $context) + [
                'source' => $activity->videosource,
                'template' => $plugin->get_player_template(),
            ];
    }
}
