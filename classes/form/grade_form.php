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
 * Teacher grading form.
 *
 * @package   mod_videosummary
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videosummary\form;

use moodleform;

/**
 * Class grade_form.
 */
class grade_form extends moodleform {
    /**
     * Method definition.
     *
     * @return void Return value.
     */
    protected function definition(): void {
        $mform = $this->_form;
        $criteria = $this->_customdata['criteria'];
        foreach ($criteria as $index => $criterion) {
            $name = 'criterion_' . $index;
            $mform->addElement('text', $name, $criterion, ['size' => 8]);
            $mform->setType($name, PARAM_FLOAT);
            $mform->addRule($name, null, 'numeric', null, 'client');
            $mform->addRule($name, get_string('required'), 'required', null, 'client');
        }
        $mform->addElement('editor', 'feedbackeditor', get_string('feedback', 'videosummary'), null, [
            'subdirs' => 0,
            'maxfiles' => 0,
            'context' => $this->_customdata['context'],
        ]);
        $mform->setType('feedbackeditor', PARAM_RAW);
        $this->add_action_buttons(true, get_string('savegrade', 'videosummary'));
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
        foreach ($this->_customdata['criteria'] as $index => $criterion) {
            $value = (float)($data['criterion_' . $index] ?? -1);
            if ($value < 0 || $value > 100) {
                $errors['criterion_' . $index] = get_string('criteriascoreinvalid', 'videosummary');
            }
        }
        return $errors;
    }
}
