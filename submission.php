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
 * Draft/final student summary workflow.
 *
 * @package   mod_videosummary
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/classes/form/submission_form.php');

$id = required_param('id', PARAM_INT);
$cm = get_coursemodule_from_id('videosummary', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$activity = $DB->get_record('videosummary', ['id' => $cm->instance], '*', MUST_EXIST);
require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/videosummary:submit', $context);

$PAGE->set_url('/mod/videosummary/submission.php', ['id' => $cm->id]);
$PAGE->set_title(get_string('editsummary', 'videosummary'));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->requires->js_call_amd('mod_videosummary/tracker', 'init');
$PAGE->requires->js_call_amd('mod_videosummary/reference_editor', 'init');

$submission = $DB->get_record('videosummary_submissions', [
    'videosummaryid' => $activity->id,
    'userid' => $USER->id,
]);
if ($submission && $submission->status === 'submitted') {
    redirect(new moodle_url('/mod/videosummary/view.php', ['id' => $cm->id]), get_string('summarylocked', 'videosummary'));
}

$progress = $DB->get_record('videosummary_progress', [
    'videosummaryid' => $activity->id,
    'userid' => $USER->id,
]);

$referencevalues = [];
if ($submission) {
    foreach ($DB->get_records('videosummary_references',
        ['submissionid' => $submission->id], 'sortorder ASC, id ASC') as $reference) {
        $referencevalues[] = [
            'start' => \mod_videosummary\summary_manager::format_time((float)$reference->starttime),
            'end' => abs((float)$reference->endtime - (float)$reference->starttime) < 0.5
                ? '' : \mod_videosummary\summary_manager::format_time((float)$reference->endtime),
            'label' => $reference->label,
            'note' => $reference->note,
        ];
    }
}
$referencesjson = json_encode($referencevalues, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$form = new \mod_videosummary\form\submission_form(null, [
    'activity' => $activity,
    'context' => $context,
    'referencesjson' => $referencesjson,
]);
if ($submission) {
    $form->set_data((object)[
        'summaryeditor' => ['text' => $submission->summarytext, 'format' => $submission->summaryformat],
        'referencesjson' => $referencesjson,
    ]);
}

if ($form->is_cancelled()) {
    redirect(new moodle_url('/mod/videosummary/view.php', ['id' => $cm->id]));
}
if ($data = $form->get_data()) {
    $isfinal = !empty($data->submitfinal);
    $html = (string)($data->summaryeditor['text'] ?? '');
    $format = (int)($data->summaryeditor['format'] ?? FORMAT_HTML);
    $references = \mod_videosummary\summary_manager::parse_references((string)$data->referencesjson) ?? [];
    $wordcount = \mod_videosummary\summary_manager::word_count($html);
    $charcount = \mod_videosummary\summary_manager::char_count($html);
    $now = time();

    $transaction = $DB->start_delegated_transaction();
    if (!$submission) {
        $submission = (object)[
            'videosummaryid' => $activity->id,
            'userid' => $USER->id,
            'timecreated' => $now,
            'grade' => null,
            'grader' => null,
            'feedback' => '',
            'feedbackformat' => FORMAT_HTML,
            'timegraded' => 0,
        ];
    }
    $submission->status = $isfinal ? 'submitted' : 'draft';
    $submission->summarytext = $html;
    $submission->summaryformat = $format;
    $submission->wordcount = $wordcount;
    $submission->charcount = $charcount;
    $submission->referencescount = count($references);
    $submission->timemodified = $now;
    $submission->timesubmitted = $isfinal ? $now : 0;
    if (empty($submission->id)) {
        $submission->id = $DB->insert_record('videosummary_submissions', $submission);
    } else {
        $DB->update_record('videosummary_submissions', $submission);
    }

    $DB->delete_records('videosummary_references', ['submissionid' => $submission->id]);
    foreach ($references as $sortorder => $reference) {
        $DB->insert_record('videosummary_references', (object)[
            'submissionid' => $submission->id,
            'starttime' => $reference['starttime'],
            'endtime' => $reference['endtime'],
            'label' => $reference['label'],
            'note' => $reference['note'],
            'sortorder' => $sortorder,
        ]);
    }
    $transaction->allow_commit();

    $completion = new completion_info($course);
    if ($completion->is_enabled($cm)) {
        $completion->update_state($cm, COMPLETION_UNKNOWN, $USER->id);
    }
    if ($isfinal) {
        $event = \mod_videosummary\event\summary_submitted::create([
            'objectid' => $submission->id,
            'context' => $context,
            'relateduserid' => $USER->id,
        ]);
        $event->trigger();
    }
    redirect(
        new moodle_url('/mod/videosummary/view.php', ['id' => $cm->id]),
        get_string($isfinal ? 'summarysubmitted' : 'draftsaved', 'videosummary'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('editsummary', 'videosummary'));
echo videosummary_render_player($activity, $cm, $context, $progress ?: null);
$form->display();
echo $OUTPUT->footer();
