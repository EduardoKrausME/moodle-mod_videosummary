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
 * Student summary submission form.
 *
 * @package   mod_videosummary
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videosummary\form;

use mod_videosummary\summary_manager;
use moodleform;

/**
 * Class submission_form.
 */
class submission_form extends moodleform {
    /**
     * Method definition.
     *
     * @return void Return value.
     */
    protected function definition(): void {
        global $OUTPUT;
        $mform = $this->_form;
        $activity = $this->_customdata['activity'];
        $referencesjson = $this->_customdata['referencesjson'] ?? '[]';

        $mform->addElement('html', $OUTPUT->notification(
            get_string(summary_manager::instruction_key($activity->summaryformat), 'videosummary'),
            'info',
            false
        ));
        $mform->addElement('editor', 'summaryeditor', get_string('summarytext', 'videosummary'), null, [
            'subdirs' => 0,
            'maxfiles' => 0,
            'context' => $this->_customdata['context'],
        ]);
        $mform->setType('summaryeditor', PARAM_RAW);
        $mform->addRule('summaryeditor', get_string('required'), 'required', null, 'client');

        $mform->addElement('hidden', 'referencesjson', $referencesjson);
        $mform->setType('referencesjson', PARAM_RAW);
        $mform->addElement('html', $OUTPUT->render_from_template('mod_videosummary/reference_editor', [
            'existingjson' => $referencesjson,
        ]));

        $buttons = [];
        $buttons[] = $mform->createElement('submit', 'savedraft',
            get_string('savedraft', 'videosummary'));
        $buttons[] = $mform->createElement('submit', 'submitfinal',
            get_string('submitfinal', 'videosummary'), ['class' => 'btn-primary']);
        $buttons[] = $mform->createElement('cancel');
        $mform->addGroup($buttons, 'buttonar', '', [' '], false);
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
        $references = summary_manager::parse_references((string)($data['referencesjson'] ?? ''));
        if ($references === null) {
            $errors['referencesjson'] = get_string('referenceinvalid', 'videosummary');
            return $errors;
        }
        if (!empty($data['submitfinal'])) {
            $html = (string)($data['summaryeditor']['text'] ?? '');
            $errors += summary_manager::validate_final($this->_customdata['activity'], $html, $references);
        }
        return $errors;
    }
}
