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
 * Per-submission grading page.
 *
 * @package   mod_videosummary
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/classes/form/grade_form.php');

$id = required_param('id', PARAM_INT);
$userid = required_param('userid', PARAM_INT);
$cm = get_coursemodule_from_id('videosummary', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$activity = $DB->get_record('videosummary', ['id' => $cm->instance], '*', MUST_EXIST);
require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/videosummary:grade', $context);
$user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);
$submission = $DB->get_record('videosummary_submissions', [
    'videosummaryid' => $activity->id,
    'userid' => $userid,
    'status' => 'submitted',
], '*', MUST_EXIST);

$PAGE->set_url('/mod/videosummary/grade.php', ['id' => $cm->id, 'userid' => $userid]);
$PAGE->set_title(get_string('grading', 'videosummary'));
$PAGE->set_heading(format_string($course->fullname));

$criteria = \mod_videosummary\summary_manager::criteria((string)$activity->gradingcriteria);
if (!$criteria) {
    $criteria = [get_string('summarytext', 'videosummary')];
}
$existinggrades = $DB->get_records('videosummary_cgrades', ['submissionid' => $submission->id]);
$bykey = [];
foreach ($existinggrades as $item) {
    $bykey[$item->criterionkey] = $item;
}

$form = new \mod_videosummary\form\grade_form(null, [
    'criteria' => $criteria,
    'context' => $context,
]);
$defaults = ['feedbackeditor' => ['text' => $submission->feedback, 'format' => $submission->feedbackformat]];
foreach ($criteria as $index => $criterion) {
    $key = 'c' . $index;
    $defaults['criterion_' . $index] = isset($bykey[$key]) ? (float)$bykey[$key]->score : '';
}
$form->set_data((object)$defaults);

if ($form->is_cancelled()) {
    redirect(new moodle_url('/mod/videosummary/report.php', ['id' => $cm->id]));
}
if ($data = $form->get_data()) {
    $scores = [];
    $transaction = $DB->start_delegated_transaction();
    $DB->delete_records('videosummary_cgrades', ['submissionid' => $submission->id]);
    foreach ($criteria as $index => $criterion) {
        $score = max(0.0, min(100.0, (float)$data->{'criterion_' . $index}));
        $scores[] = $score;
        $DB->insert_record('videosummary_cgrades', (object)[
            'submissionid' => $submission->id,
            'criterionkey' => 'c' . $index,
            'criterion' => $criterion,
            'score' => $score,
        ]);
    }
    $average = $scores ? array_sum($scores) / count($scores) : 0.0;
    $submission->grade = ((float)$activity->grade * $average) / 100.0;
    $submission->grader = $USER->id;
    $submission->feedback = (string)($data->feedbackeditor['text'] ?? '');
    $submission->feedbackformat = (int)($data->feedbackeditor['format'] ?? FORMAT_HTML);
    $submission->timegraded = time();
    $submission->timemodified = time();
    $DB->update_record('videosummary_submissions', $submission);
    $transaction->allow_commit();

    videosummary_update_grades($activity, $userid, false);
    $event = \mod_videosummary\event\summary_graded::create([
        'objectid' => $submission->id,
        'context' => $context,
        'relateduserid' => $userid,
    ]);
    $event->trigger();
    redirect(
        new moodle_url('/mod/videosummary/report.php', ['id' => $cm->id]),
        get_string('gradesaved', 'videosummary'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

$references = [];
foreach ($DB->get_records('videosummary_references', ['submissionid' => $submission->id], 'sortorder ASC, id ASC') as $reference) {
    $start = \mod_videosummary\summary_manager::format_time((float)$reference->starttime);
    $end = \mod_videosummary\summary_manager::format_time((float)$reference->endtime);
    $references[] = [
        'range' => abs((float)$reference->endtime - (float)$reference->starttime) < 0.5 ? $start : $start . '–' . $end,
        'label' => format_string($reference->label),
        'note' => format_string($reference->note),
    ];
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('grading', 'videosummary'));
echo $OUTPUT->render_from_template('mod_videosummary/grade_summary', [
    'fullname' => fullname($user),
    'summaryhtml' => format_text($submission->summarytext, $submission->summaryformat, ['context' => $context]),
    'references' => $references,
    'hasreferences' => !empty($references),
]);
$form->display();
echo $OUTPUT->footer();
