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
 * Main student view for Video Summary.
 *
 * @package   mod_videosummary
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_videosummary\summary_manager;

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

$id = required_param('id', PARAM_INT);
$cm = get_coursemodule_from_id('videosummary', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$activity = $DB->get_record('videosummary', ['id' => $cm->instance], '*', MUST_EXIST);
require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/videosummary:view', $context);

$PAGE->set_url('/mod/videosummary/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($activity->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->requires->js_call_amd('mod_videosummary/tracker', 'init');

$event = \mod_videosummary\event\course_module_viewed::create([
    'objectid' => $activity->id,
    'context' => $context,
]);
$event->add_record_snapshot('videosummary', $activity);
$event->trigger();

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$progress = $DB->get_record('videosummary_progress', [
    'videosummaryid' => $activity->id,
    'userid' => $USER->id,
]);
$submission = $DB->get_record('videosummary_submissions', [
    'videosummaryid' => $activity->id,
    'userid' => $USER->id,
]);

$references = [];
if ($submission) {
    foreach ($DB->get_records('videosummary_references',
        ['submissionid' => $submission->id], 'sortorder ASC, id ASC') as $reference) {
        $start = summary_manager::format_time((float)$reference->starttime);
        $end = summary_manager::format_time((float)$reference->endtime);
        $references[] = [
            'starttime' => (float)$reference->starttime,
            'range' => abs((float)$reference->endtime - (float)$reference->starttime) < 0.5 ? $start : $start . '–' . $end,
            'label' => format_string($reference->label),
            'note' => format_string($reference->note),
        ];
    }
}

$status = 'notstarted';
if ($submission) {
    $status = $submission->grade !== null ? 'graded' : $submission->status;
}

$data = [
    'name' => format_string($activity->name),
    'hasintro' => trim((string)$activity->intro) !== '',
    'intro' => format_module_intro('videosummary', $activity, $cm->id, false),
    'playerhtml' => videosummary_render_player($activity, $cm, $context, $progress ?: null),
    'canviewreport' => has_capability('mod/videosummary:viewreport', $context),
    'reporturl' => (new moodle_url('/mod/videosummary/report.php', ['id' => $cm->id]))->out(false),
    'cansubmit' => has_capability('mod/videosummary:submit', $context),
    'editurl' => (new moodle_url('/mod/videosummary/submission.php', ['id' => $cm->id]))->out(false),
    'submitted' => $submission && $submission->status === 'submitted',
    'hassubmission' => (bool)$submission,
    'statuslabel' => get_string('status_' . $status, 'videosummary'),
    'references' => $references,
    'hasreferences' => !empty($references),
];

if ($submission) {
    $data['summaryhtml'] = format_text($submission->summarytext, $submission->summaryformat, ['context' => $context]);
    $data['wordcount'] = (int)$submission->wordcount;
    $data['charcount'] = (int)$submission->charcount;
    $data['referencescount'] = (int)$submission->referencescount;
    $data['hasgrade'] = $submission->grade !== null;
    if ($submission->grade !== null) {
        $data['gradedisplay'] = format_float((float)$submission->grade, 2) . ' / ' . format_float((float)$activity->grade, 2);
        $data['hasfeedback'] = trim((string)$submission->feedback) !== '';
        $data['feedbackhtml'] = format_text($submission->feedback, $submission->feedbackformat, ['context' => $context]);
    }
}

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('mod_videosummary/view', $data);
echo $OUTPUT->footer();
