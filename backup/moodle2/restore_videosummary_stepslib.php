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
 * Video Summary restore structure.
 *
 * @package   mod_videosummary
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class restore_videosummary_activity_structure_step.
 */
class restore_videosummary_activity_structure_step extends restore_activity_structure_step {
    /**
     * Method define_structure.
     *
     * @return array Return value.
     */
    protected function define_structure(): array {
        $paths = [];
        $paths[] = new restore_path_element('videosummary', '/activity/videosummary');
        if ($this->get_setting_value('userinfo')) {
            $paths[] = new restore_path_element('videosummary_progress',
                '/activity/videosummary/progresses/progress');
            $paths[] = new restore_path_element('videosummary_submission',
                '/activity/videosummary/submissions/submission');
            $paths[] = new restore_path_element('videosummary_reference',
                '/activity/videosummary/submissions/submission/references/reference');
            $paths[] = new restore_path_element('videosummary_criteriongrade',
                '/activity/videosummary/submissions/submission/criteriongrades/criteriongrade');
        }
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Method process_videosummary.
     *
     * @param mixed $data Parameter data.
     * @return void Return value.
     */
    protected function process_videosummary($data): void {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $newid = $DB->insert_record('videosummary', $data);
        $this->apply_activity_instance($newid);
        $this->set_mapping('videosummary', $oldid, $newid, true);
    }

    /**
     * Method process_videosummary_progress.
     *
     * @param mixed $data Parameter data.
     * @return void Return value.
     */
    protected function process_videosummary_progress($data): void {
        global $DB;
        $data = (object)$data;
        $data->videosummaryid = $this->get_new_parentid('videosummary');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        if ($data->userid) {
            $DB->insert_record('videosummary_progress', $data);
        }
    }

    /**
     * Method process_videosummary_submission.
     *
     * @param mixed $data Parameter data.
     * @return void Return value.
     */
    protected function process_videosummary_submission($data): void {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;
        $data->videosummaryid = $this->get_new_parentid('videosummary');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->grader = $data->grader ? $this->get_mappingid('user', $data->grader) : null;
        foreach (['timecreated', 'timemodified', 'timesubmitted', 'timegraded'] as $field) {
            if (!empty($data->{$field})) {
                $data->{$field} = $this->apply_date_offset($data->{$field});
            }
        }
        if (!$data->userid) {
            return;
        }
        $newid = $DB->insert_record('videosummary_submissions', $data);
        $this->set_mapping('videosummary_submission', $oldid, $newid, true);
    }

    /**
     * Method process_videosummary_reference.
     *
     * @param mixed $data Parameter data.
     * @return void Return value.
     */
    protected function process_videosummary_reference($data): void {
        global $DB;
        $data = (object)$data;
        $submissionid = $this->get_new_parentid('videosummary_submission');
        if (!$submissionid) {
            return;
        }
        $data->submissionid = $submissionid;
        $DB->insert_record('videosummary_references', $data);
    }

    /**
     * Method process_videosummary_criteriongrade.
     *
     * @param mixed $data Parameter data.
     * @return void Return value.
     */
    protected function process_videosummary_criteriongrade($data): void {
        global $DB;
        $data = (object)$data;
        $submissionid = $this->get_new_parentid('videosummary_submission');
        if (!$submissionid) {
            return;
        }
        $data->submissionid = $submissionid;
        $DB->insert_record('videosummary_cgrades', $data);
    }

    /**
     * Method after_execute.
     *
     * @return void Return value.
     */
    protected function after_execute(): void {
        $this->add_related_files('mod_videosummary', 'poster', null);
        $this->add_related_files('mod_videosummary', 'video', null);
    }
}
