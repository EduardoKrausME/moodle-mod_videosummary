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
 * Privacy provider for Video Summary.
 *
 * @package   mod_videosummary
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videosummary\privacy;

use context;
use context_module;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\helper;
use core_privacy\local\request\plugin\provider as plugin_provider;
use core_privacy\local\request\core_userlist_provider;
use core_privacy\local\request\transform;
use core_privacy\local\request\writer;
use core_privacy\local\request\userlist;

/**
 * Class provider.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    plugin_provider,
    core_userlist_provider {

    /**
     * Method get_metadata.
     *
     * @param collection $collection Parameter collection.
     * @return collection Return value.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('videosummary_progress', [
            'userid' => 'privacy:metadata:progress:userid',
            'segments' => 'privacy:metadata:progress:segments',
            'lastposition' => 'privacy:metadata:progress:lastposition',
            'percent' => 'privacy:metadata:progress:percent',
        ], 'privacy:metadata:progress');
        $collection->add_database_table('videosummary_submissions', [
            'userid' => 'privacy:metadata:submissions:userid',
            'summarytext' => 'privacy:metadata:submissions:summarytext',
            'status' => 'privacy:metadata:submissions:status',
            'grade' => 'privacy:metadata:submissions:grade',
            'feedback' => 'privacy:metadata:submissions:feedback',
            'grader' => 'privacy:metadata:submissions:grader',
        ], 'privacy:metadata:submissions');
        $collection->add_database_table('videosummary_cgrades', [
            'criterion' => 'privacy:metadata:cgrades:criterion',
            'score' => 'privacy:metadata:cgrades:score',
        ], 'privacy:metadata:cgrades');
        $collection->add_database_table('videosummary_references', [
            'starttime' => 'privacy:metadata:references:starttime',
            'endtime' => 'privacy:metadata:references:endtime',
            'label' => 'privacy:metadata:references:label',
            'note' => 'privacy:metadata:references:note',
        ], 'privacy:metadata:references');
        return $collection;
    }

    /**
     * Method get_contexts_for_userid.
     *
     * @param int $userid Parameter userid.
     * @return contextlist Return value.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        $sql = "SELECT ctx.id
                  FROM {context} ctx
                  JOIN {course_modules} cm ON cm.id = ctx.instanceid AND ctx.contextlevel = :contextlevel
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                  JOIN {videosummary} vs ON vs.id = cm.instance
             LEFT JOIN {videosummary_progress} vp ON vp.videosummaryid = vs.id AND vp.userid = :progressuserid
             LEFT JOIN {videosummary_submissions} sub ON sub.videosummaryid = vs.id AND sub.userid = :submissionuserid
                 WHERE vp.id IS NOT NULL OR sub.id IS NOT NULL";
        $contextlist->add_from_sql($sql, [
            'contextlevel' => CONTEXT_MODULE,
            'modname' => 'videosummary',
            'progressuserid' => $userid,
            'submissionuserid' => $userid,
        ]);
        return $contextlist;
    }

    /**
     * Method get_users_in_context.
     *
     * @param userlist $userlist Parameter userlist.
     * @return void Return value.
     */
    public static function get_users_in_context(userlist $userlist): void {
        $context = $userlist->get_context();
        if (!$context instanceof context_module) {
            return;
        }
        $sql = "SELECT vp.userid
                  FROM {course_modules} cm
                  JOIN {videosummary} vs ON vs.id = cm.instance
                  JOIN {videosummary_progress} vp ON vp.videosummaryid = vs.id
                 WHERE cm.id = :cmid
                 UNION
                SELECT sub.userid
                  FROM {course_modules} cm
                  JOIN {videosummary} vs ON vs.id = cm.instance
                  JOIN {videosummary_submissions} sub ON sub.videosummaryid = vs.id
                 WHERE cm.id = :cmid2";
        $userlist->add_from_sql('userid', $sql, ['cmid' => $context->instanceid, 'cmid2' => $context->instanceid]);
    }

    /**
     * Method export_user_data.
     *
     * @param approved_contextlist $contextlist Parameter contextlist.
     * @return void Return value.
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof context_module) {
                continue;
            }
            $cm = get_coursemodule_from_id('videosummary', $context->instanceid, 0, false, IGNORE_MISSING);
            if (!$cm) {
                continue;
            }
            $activity = $DB->get_record('videosummary', ['id' => $cm->instance]);
            if (!$activity) {
                continue;
            }
            $data = (object)[];
            $progress = $DB->get_record('videosummary_progress', [
                'videosummaryid' => $activity->id,
                'userid' => $userid,
            ]);
            if ($progress) {
                $data->progress = (object)[
                    'percent' => (float)$progress->percent,
                    'lastposition' => (float)$progress->lastposition,
                    'segments' => json_decode((string)$progress->segments, true),
                    'timemodified' => transform::datetime($progress->timemodified),
                ];
            }
            $submission = $DB->get_record('videosummary_submissions', [
                'videosummaryid' => $activity->id,
                'userid' => $userid,
            ]);
            if ($submission) {
                $data->submission = (object)[
                    'status' => $submission->status,
                    'summary' => format_text($submission->summarytext, $submission->summaryformat, ['context' => $context]),
                    'wordcount' => (int)$submission->wordcount,
                    'charcount' => (int)$submission->charcount,
                    'grade' => $submission->grade,
                    'feedback' => format_text($submission->feedback, $submission->feedbackformat, ['context' => $context]),
                    'timemodified' => transform::datetime($submission->timemodified),
                ];
                $data->criteriongrades = array_values(array_map(static function ($grade) {
                    return (object)[
                        'criterion' => $grade->criterion,
                        'score' => (float)$grade->score,
                    ];
                }, $DB->get_records('videosummary_cgrades', ['submissionid' => $submission->id], 'criterionkey ASC')));
                $data->references = array_values(array_map(static function ($reference) {
                    return (object)[
                        'starttime' => (float)$reference->starttime,
                        'endtime' => (float)$reference->endtime,
                        'label' => $reference->label,
                        'note' => $reference->note,
                    ];
                }, $DB->get_records('videosummary_references', ['submissionid' => $submission->id], 'sortorder ASC, id ASC')));
            }
            if (!empty((array)$data)) {
                writer::with_context($context)->export_data([], $data);
                helper::export_context_files($context, $userid);
            }
        }
    }

    /**
     * Method delete_data_for_all_users_in_context.
     *
     * @param context $context Parameter context.
     * @return void Return value.
     */
    public static function delete_data_for_all_users_in_context(context $context): void {
        if (!$context instanceof context_module) {
            return;
        }
        self::delete_for_context($context, null);
    }

    /**
     * Method delete_data_for_user.
     *
     * @param approved_contextlist $contextlist Parameter contextlist.
     * @return void Return value.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context instanceof context_module) {
                self::delete_for_context($context, $userid);
            }
        }
    }

    /**
     * Method delete_data_for_users.
     *
     * @param approved_userlist $userlist Parameter userlist.
     * @return void Return value.
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        $context = $userlist->get_context();
        if (!$context instanceof context_module) {
            return;
        }
        foreach ($userlist->get_userids() as $userid) {
            self::delete_for_context($context, (int)$userid);
        }
    }

    /**
     * Method delete_for_context.
     *
     * @param context_module $context Parameter context.
     * @param ?int $userid Parameter userid.
     * @return void Return value.
     */
    private static function delete_for_context(context_module $context, ?int $userid): void {
        global $DB;
        $cm = get_coursemodule_from_id('videosummary', $context->instanceid, 0, false, IGNORE_MISSING);
        if (!$cm) {
            return;
        }
        $submissionconditions = ['videosummaryid' => $cm->instance];
        $progressconditions = ['videosummaryid' => $cm->instance];
        if ($userid !== null) {
            $submissionconditions['userid'] = $userid;
            $progressconditions['userid'] = $userid;
        }
        $submissionids = $DB->get_fieldset_select(
            'videosummary_submissions',
            'id',
            'videosummaryid = :activity' . ($userid !== null ? ' AND userid = :userid' : ''),
            $userid !== null ? ['activity' => $cm->instance, 'userid' => $userid] : ['activity' => $cm->instance]
        );
        if ($submissionids) {
            [$insql, $params] = $DB->get_in_or_equal($submissionids, SQL_PARAMS_NAMED, 'privacy');
            $DB->delete_records_select('videosummary_references', "submissionid {$insql}", $params);
            $DB->delete_records_select('videosummary_cgrades', "submissionid {$insql}", $params);
        }
        $DB->delete_records('videosummary_submissions', $submissionconditions);
        $DB->delete_records('videosummary_progress', $progressconditions);
    }
}
