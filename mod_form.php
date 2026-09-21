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
 * Activity settings form for Video Summary.
 *
 * @package   mod_videosummary
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/course/moodleform_mod.php');

use mod_videosummary\source\manager as source_manager;

/**
 * Class mod_videosummary_mod_form.
 */
class mod_videosummary_mod_form extends moodleform_mod {
    /**
     * Method definition.
     *
     * @return void Return value.
     */
    public function definition(): void {
        $mform = $this->_form;
        $sources = new source_manager();
        $sourceoptions = $sources->get_options();
        if (!$sourceoptions) {
            throw new moodle_exception('sourcepluginmissing', 'videosummary', '', 'none');
        }

        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('videosummaryname', 'videosummary'), ['size' => 64]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $this->standard_intro_elements();

        $mform->addElement('header', 'sourceheader', get_string('sourceheader', 'videosummary'));
        $mform->addElement('select', 'videosource', get_string('videosource', 'videosummary'), $sourceoptions);
        $mform->setType('videosource', PARAM_PLUGIN);
        $mform->setDefault('videosource', $sources->get_default_source());
        $sources->add_form_elements($mform, 'videosource');

        $mform->addElement('filemanager', 'poster', get_string('poster', 'videosummary'), null, [
            'subdirs' => 0,
            'maxfiles' => 1,
            'accepted_types' => ['image'],
        ]);
        $noposter = $sources->get_sources_without_poster();
        if ($noposter) {
            $mform->hideIf('poster', 'videosource', 'in', $noposter);
        }

        $mform->addElement('header', 'playbackheader', get_string('playbackheader', 'videosummary'));
        $mform->addElement('select', 'resumeplayback', get_string('resumeplayback', 'videosummary'), [
            1 => get_string('resumeautomatic', 'videosummary'),
            2 => get_string('resumeask', 'videosummary'),
            0 => get_string('resumefromstart', 'videosummary'),
        ]);
        $mform->setDefault('resumeplayback', 1);
        $mform->addElement('selectyesno', 'allowseek', get_string('allowseek', 'videosummary'));
        $mform->setDefault('allowseek', 1);

        $mform->addElement('header', 'summarysettings', get_string('summarysettings', 'videosummary'));
        $formats = [
            'free' => get_string('format_free', 'videosummary'),
            'maxwords' => get_string('format_maxwords', 'videosummary'),
            'fivepoints' => get_string('format_fivepoints', 'videosummary'),
            'threeconcepts' => get_string('format_threeconcepts', 'videosummary'),
            'conclusion' => get_string('format_conclusion', 'videosummary'),
            'chapters' => get_string('format_chapters', 'videosummary'),
        ];
        $mform->addElement('select', 'summaryformat', get_string('summaryformat', 'videosummary'), $formats);
        $mform->setDefault('summaryformat', 'free');
        $mform->setType('summaryformat', PARAM_ALPHA);

        $mform->addElement('text', 'wordlimit', get_string('wordlimit', 'videosummary'), ['size' => 10]);
        $mform->setType('wordlimit', PARAM_INT);
        $mform->setDefault('wordlimit', 0);
        $mform->addHelpButton('wordlimit', 'wordlimit', 'videosummary');

        $mform->addElement('text', 'charlimit', get_string('charlimit', 'videosummary'), ['size' => 10]);
        $mform->setType('charlimit', PARAM_INT);
        $mform->setDefault('charlimit', 0);
        $mform->addHelpButton('charlimit', 'charlimit', 'videosummary');

        $mform->addElement('text', 'minreferences', get_string('minreferences', 'videosummary'), ['size' => 10]);
        $mform->setType('minreferences', PARAM_INT);
        $mform->setDefault('minreferences', 0);
        $mform->addHelpButton('minreferences', 'minreferences', 'videosummary');

        $mform->addElement('textarea', 'gradingcriteria',
            get_string('gradingcriteria', 'videosummary'), ['rows' => 6, 'cols' => 60]);
        $mform->setType('gradingcriteria', PARAM_TEXT);
        $mform->setDefault('gradingcriteria',
            get_string('defaultcriteria', 'videosummary'));
        $mform->addHelpButton('gradingcriteria', 'gradingcriteria', 'videosummary');

        $mform->addElement('text', 'grade',
            get_string('maxgrade', 'videosummary'), ['size' => 10]);
        $mform->setType('grade', PARAM_FLOAT);
        $mform->setDefault('grade', 100);

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    /**
     * Method data_preprocessing.
     *
     * @param mixed $defaultvalues Parameter defaultvalues.
     * @return void Return value.
     */
    public function data_preprocessing(&$defaultvalues): void {
        parent::data_preprocessing($defaultvalues);
        if (empty($this->current->coursemodule)) {
            return;
        }
        $context = context_module::instance($this->current->coursemodule);
        $draftid = file_get_submitted_draft_itemid('poster');
        file_prepare_draft_area($draftid, $context->id, 'mod_videosummary', 'poster', 0, [
            'subdirs' => 0,
            'maxfiles' => 1,
            'accepted_types' => ['image'],
        ]);
        $defaultvalues['poster'] = $draftid;
        (new source_manager())->prepare_form_data($defaultvalues, $context);
    }

    /**
     * Method validation.
     *
     * @param mixed $data Parameter data.
     * @param mixed $files Parameter files.
     * @return array Return value.
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        $errors += (new source_manager())->validation($data, $files);
        if ((int)($data['wordlimit'] ?? 0) < 0) {
            $errors['wordlimit'] = get_string('invaliddata', 'error');
        }
        if ((int)($data['charlimit'] ?? 0) < 0) {
            $errors['charlimit'] = get_string('invaliddata', 'error');
        }
        if ((int)($data['minreferences'] ?? 0) < 0) {
            $errors['minreferences'] = get_string('invaliddata', 'error');
        }
        if (($data['summaryformat'] ?? '') === 'maxwords' && (int)($data['wordlimit'] ?? 0) <= 0) {
            $errors['wordlimit'] = get_string('required');
        }
        if ((float)($data['grade'] ?? 0) < 0) {
            $errors['grade'] = get_string('invaliddata', 'error');
        }
        return $errors;
    }

    /**
     * Method add_completion_rules.
     *
     * @return array Return value.
     */
    public function add_completion_rules(): array {
        $mform = $this->_form;
        $mform->addElement('text', 'completionpercent', get_string('completionpercent', 'videosummary'), ['size' => 5]);
        $mform->setType('completionpercent', PARAM_INT);
        $mform->setDefault('completionpercent', 80);
        $mform->addElement('selectyesno', 'completionsummary', get_string('completionsummary', 'videosummary'));
        $mform->setDefault('completionsummary', 1);
        return ['completionpercent', 'completionsummary'];
    }

    /**
     * Method completion_rule_enabled.
     *
     * @param mixed $data Parameter data.
     * @return bool Return value.
     */
    public function completion_rule_enabled($data): bool {
        return !empty($data['completionpercent']) || !empty($data['completionsummary']);
    }
}
