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
 * Teacher progress and submission report.
 *
 * @package   mod_videosummary
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$cm = get_coursemodule_from_id('videosummary', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$activity = $DB->get_record('videosummary', ['id' => $cm->instance], '*', MUST_EXIST);
require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/videosummary:viewreport', $context);

$PAGE->set_url('/mod/videosummary/report.php', ['id' => $cm->id]);
$PAGE->set_title(get_string('report', 'videosummary'));
$PAGE->set_heading(format_string($course->fullname));

$users = get_enrolled_users($context, 'mod/videosummary:submit', 0, 'u.*', 'u.lastname, u.firstname');
$rows = [];
foreach ($users as $user) {
    $progress = $DB->get_record('videosummary_progress', ['videosummaryid' => $activity->id, 'userid' => $user->id]);
    $submission = $DB->get_record('videosummary_submissions', ['videosummaryid' => $activity->id, 'userid' => $user->id]);
    $status = 'notstarted';
    if ($submission) {
        $status = $submission->grade !== null ? 'graded' : $submission->status;
    }
    $rows[] = [
        'fullname' => fullname($user),
        'profileurl' => (new moodle_url('/user/view.php', ['id' => $user->id, 'course' => $course->id]))->out(false),
        'percent' => $progress ? (int)round((float)$progress->percent) : 0,
        'started' => $submission ? get_string('yes', 'videosummary') : get_string('no', 'videosummary'),
        'delivered' => $submission && $submission->status === 'submitted' ?
            get_string('yes', 'videosummary') :
            get_string('no', 'videosummary'),
        'references' => $submission ? (int)$submission->referencescount : 0,
        'grade' => $submission && $submission->grade !== null
            ? format_float((float)$submission->grade, 2) . ' / ' . format_float((float)$activity->grade, 2)
            : get_string('notgraded', 'videosummary'),
        'status' => get_string('status_' . $status, 'videosummary'),
        'hasgradeurl' => $submission && $submission->status === 'submitted' && has_capability('mod/videosummary:grade', $context),
        'gradeurl' => $submission ? (new moodle_url('/mod/videosummary/grade.php',
            ['id' => $cm->id, 'userid' => $user->id]))->out(false) : '',
    ];
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('report', 'videosummary'));
echo $OUTPUT->render_from_template('mod_videosummary/report', ['rows' => $rows]);
echo $OUTPUT->footer();
