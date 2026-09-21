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
 * Video Summary backup structure.
 *
 * @package   mod_videosummary
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class backup_videosummary_activity_structure_step.
 */
class backup_videosummary_activity_structure_step extends backup_activity_structure_step {
    /**
     * Method define_structure.
     *
     * @return mixed Return value.
     */
    protected function define_structure() {
        $userinfo = $this->get_setting_value('userinfo');

        $activity = new backup_nested_element('videosummary', ['id'], [
            'name', 'intro', 'introformat', 'videosource', 'sourceconfig', 'videourl',
            'resumeplayback', 'allowseek', 'summaryformat', 'wordlimit', 'charlimit',
            'minreferences', 'gradingcriteria', 'grade', 'completionpercent', 'completionsummary',
            'timecreated', 'timemodified',
        ]);
        $progresses = new backup_nested_element('progresses');
        $progress = new backup_nested_element('progress', ['id'], [
            'userid', 'segments', 'duration', 'uniquewatched', 'percent', 'lastposition',
            'completed', 'timecreated', 'timemodified',
        ]);
        $submissions = new backup_nested_element('submissions');
        $submission = new backup_nested_element('submission', ['id'], [
            'userid', 'status', 'summarytext', 'summaryformat', 'wordcount', 'charcount',
            'referencescount', 'grade', 'grader', 'feedback', 'feedbackformat', 'timecreated',
            'timemodified', 'timesubmitted', 'timegraded',
        ]);
        $references = new backup_nested_element('references');
        $reference = new backup_nested_element('reference', ['id'], [
            'starttime', 'endtime', 'label', 'note', 'sortorder',
        ]);
        $criteriongrades = new backup_nested_element('criteriongrades');
        $criteriongrade = new backup_nested_element('criteriongrade', ['id'], [
            'criterionkey', 'criterion', 'score',
        ]);

        $activity->add_child($progresses);
        $progresses->add_child($progress);
        $activity->add_child($submissions);
        $submissions->add_child($submission);
        $submission->add_child($references);
        $references->add_child($reference);
        $submission->add_child($criteriongrades);
        $criteriongrades->add_child($criteriongrade);

        $activity->set_source_table('videosummary', ['id' => backup::VAR_ACTIVITYID]);
        if ($userinfo) {
            $progress->set_source_table('videosummary_progress', ['videosummaryid' => backup::VAR_PARENTID]);
            $submission->set_source_table('videosummary_submissions', ['videosummaryid' => backup::VAR_PARENTID]);
            $reference->set_source_table('videosummary_references', ['submissionid' => backup::VAR_PARENTID]);
            $criteriongrade->set_source_table('videosummary_cgrades', ['submissionid' => backup::VAR_PARENTID]);
        }

        $progress->annotate_ids('user', 'userid');
        $submission->annotate_ids('user', 'userid');
        $submission->annotate_ids('user', 'grader');
        $activity->annotate_files('mod_videosummary', 'poster', null);
        $activity->annotate_files('mod_videosummary', 'video', null);

        return $this->prepare_activity_structure($activity);
    }
}
