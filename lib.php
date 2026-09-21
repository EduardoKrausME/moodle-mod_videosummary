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
 * Core callbacks for Video Summary.
 *
 * @package   mod_videosummary
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_videosummary\source\manager as source_manager;

/**
 * Declares supported Moodle features.
 *
 * @param string $feature Feature constant.
 * @return bool|string|null
 */
function videosummary_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_ARCHETYPE:
            return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GROUPS:
            return false;
        case FEATURE_GROUPINGS:
            return false;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_MOD_PURPOSE:
            return MOD_PURPOSE_ASSESSMENT;
        default:
            return null;
    }
}

/**
 * Creates an activity instance.
 *
 * @param stdClass $data Submitted data.
 * @param mod_videosummary_mod_form|null $mform Module form.
 * @return int Instance id.
 */
function videosummary_add_instance(stdClass $data, ?mod_videosummary_mod_form $mform = null): int {
    global $DB;

    $now = time();
    $data->timecreated = $now;
    $data->timemodified = $now;
    (new source_manager())->normalise_record($data);
    $id = $DB->insert_record('videosummary', $data);
    $data->id = $id;

    $context = context_module::instance($data->coursemodule);
    videosummary_save_poster($data, $context);
    (new source_manager())->save_files($data, $context);
    videosummary_grade_item_update($data);
    return $id;
}

/**
 * Updates an activity instance.
 *
 * @param stdClass $data Submitted data.
 * @param mod_videosummary_mod_form|null $mform Module form.
 * @return bool
 */
function videosummary_update_instance(stdClass $data, ?mod_videosummary_mod_form $mform = null): bool {
    global $DB;

    $data->id = $data->instance;
    $data->timemodified = time();
    $previoussource = $DB->get_field('videosummary', 'videosource', ['id' => $data->id], MUST_EXIST);
    (new source_manager())->normalise_record($data);
    $result = $DB->update_record('videosummary', $data);

    $context = context_module::instance($data->coursemodule);
    videosummary_save_poster($data, $context);
    (new source_manager())->save_files($data, $context, $previoussource);
    videosummary_grade_item_update($data);
    return $result;
}

/**
 * Deletes an activity instance and related data.
 *
 * @param int $id Instance id.
 * @return bool
 */
function videosummary_delete_instance(int $id): bool {
    global $DB;

    $activity = $DB->get_record('videosummary', ['id' => $id]);
    if (!$activity) {
        return false;
    }

    $cm = get_coursemodule_from_instance('videosummary', $id, $activity->course, false, IGNORE_MISSING);
    if ($cm) {
        $context = context_module::instance($cm->id);
        (new source_manager())->delete_files($context);
        get_file_storage()->delete_area_files($context->id, 'mod_videosummary');
    }

    $submissionids = $DB->get_fieldset_select('videosummary_submissions', 'id', 'videosummaryid = :id', ['id' => $id]);
    if ($submissionids) {
        [$insql, $params] = $DB->get_in_or_equal($submissionids, SQL_PARAMS_NAMED, 'sub');
        $DB->delete_records_select('videosummary_references', "submissionid {$insql}", $params);
        $DB->delete_records_select('videosummary_cgrades', "submissionid {$insql}", $params);
    }
    $DB->delete_records('videosummary_submissions', ['videosummaryid' => $id]);
    $DB->delete_records('videosummary_progress', ['videosummaryid' => $id]);
    $DB->delete_records('videosummary', ['id' => $id]);
    videosummary_grade_item_delete($activity);
    return true;
}

/**
 * Saves the optional poster image.
 *
 * @param stdClass $data Activity data.
 * @param context_module $context Module context.
 * @return void
 */
function videosummary_save_poster(stdClass $data, context_module $context): void {
    if (!empty($data->poster)) {
        file_save_draft_area_files($data->poster, $context->id, 'mod_videosummary', 'poster', 0, [
            'subdirs' => 0,
            'maxfiles' => 1,
            'accepted_types' => ['image'],
        ]);
    }
}

/**
 * Returns a protected poster URL when available.
 *
 * @param context_module $context Module context.
 * @return string
 */
function videosummary_get_poster_url(context_module $context): string {
    $files = get_file_storage()->get_area_files($context->id, 'mod_videosummary', 'poster', 0, 'filename', false);
    if (!$files) {
        return '';
    }
    $file = reset($files);
    return moodle_url::make_pluginfile_url(
        $context->id,
        'mod_videosummary',
        'poster',
        0,
        $file->get_filepath(),
        $file->get_filename()
    )->out(false);
}

/**
 * Serves protected video and poster files.
 *
 * @param stdClass $course Course.
 * @param stdClass $cm Course module.
 * @param context $context Context.
 * @param string $filearea File area.
 * @param array $args File path arguments.
 * @param bool $forcedownload Force download.
 * @param array $options Options.
 * @return bool
 */
function mod_videosummary_pluginfile($course, $cm, $context, string $filearea, array $args,
                                     bool $forcedownload, array $options = []): bool {
    if ($context->contextlevel !== CONTEXT_MODULE || !in_array($filearea, ['video', 'poster'], true)) {
        return false;
    }
    require_login($course, true, $cm);
    require_capability('mod/videosummary:view', $context);
    $itemid = (int)array_shift($args);
    if ($itemid !== 0) {
        return false;
    }
    $filename = array_pop($args);
    $filepath = '/' . ($args ? implode('/', $args) . '/' : '');
    $file = get_file_storage()->get_file($context->id, 'mod_videosummary', $filearea, 0, $filepath, $filename);
    if (!$file || $file->is_directory()) {
        return false;
    }
    send_stored_file($file, 0, 0, $forcedownload, $options);
}

/**
 * Updates the gradebook item.
 *
 * @param stdClass $activity Activity.
 * @param array|null $grades Grades.
 * @return int
 */
function videosummary_grade_item_update(stdClass $activity, ?array $grades = null): int {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    $max = max(0.0, (float)$activity->grade);
    $item = [
        'itemname' => clean_param($activity->name, PARAM_NOTAGS),
        'gradetype' => $max > 0 ? GRADE_TYPE_VALUE : GRADE_TYPE_NONE,
        'grademin' => 0,
        'grademax' => $max > 0 ? $max : 100,
    ];
    return grade_update('mod/videosummary', $activity->course, 'mod', 'videosummary', $activity->id, 0, $grades, $item);
}

/**
 * Publishes stored submission grades.
 *
 * @param stdClass $activity Activity.
 * @param int $userid Optional user.
 * @param bool $nullifnone Whether to publish null when missing.
 * @return void
 */
function videosummary_update_grades(stdClass $activity, int $userid = 0, bool $nullifnone = true): void {
    global $DB;

    $conditions = ['videosummaryid' => $activity->id];
    if ($userid) {
        $conditions['userid'] = $userid;
    }
    $records = $DB->get_records('videosummary_submissions', $conditions);
    $grades = [];
    foreach ($records as $record) {
        if ($record->grade !== null) {
            $grades[$record->userid] = (object)['userid' => $record->userid, 'rawgrade' => (float)$record->grade];
        }
    }
    if (!$grades && $userid && $nullifnone) {
        $grades[$userid] = (object)['userid' => $userid, 'rawgrade' => null];
    }
    videosummary_grade_item_update($activity, $grades);
}

/**
 * Deletes the gradebook item.
 *
 * @param stdClass $activity Activity.
 * @return int
 */
function videosummary_grade_item_delete(stdClass $activity): int {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');
    return grade_update('mod/videosummary', $activity->course, 'mod', 'videosummary', $activity->id, 0, null, ['deleted' => 1]);
}

/**
 * Supplies course-module cache information.
 *
 * @param stdClass $cm Course module.
 * @return cached_cm_info|null
 */
function videosummary_get_coursemodule_info(stdClass $cm): ?cached_cm_info {
    global $DB;

    $activity = $DB->get_record('videosummary', ['id' => $cm->instance],
        'id,name,intro,introformat,completionpercent,completionsummary');
    if (!$activity) {
        return null;
    }
    $info = new cached_cm_info();
    $info->name = $activity->name;
    if ($cm->showdescription) {
        $info->content = format_module_intro('videosummary', $activity, $cm->id, false);
    }
    if ((int)$cm->completion === COMPLETION_TRACKING_AUTOMATIC) {
        $info->customdata['customcompletionrules'] = [
            'completionpercent' => (int)$activity->completionpercent,
            'completionsummary' => (bool)$activity->completionsummary,
        ];
    }
    return $info;
}

/**
 * Returns custom completion descriptions.
 *
 * @param cached_cm_info $cm Cached module info.
 * @return array
 */
function videosummary_get_completion_active_rule_descriptions(cached_cm_info $cm): array {
    if ((int)$cm->completion !== COMPLETION_TRACKING_AUTOMATIC || empty($cm->customdata['customcompletionrules'])) {
        return [];
    }
    $rules = $cm->customdata['customcompletionrules'];
    $descriptions = [];
    if (!empty($rules['completionpercent'])) {
        $descriptions[] = get_string('completiondetail:percent', 'videosummary', $rules['completionpercent']);
    }
    if (!empty($rules['completionsummary'])) {
        $descriptions[] = get_string('completiondetail:summary', 'videosummary');
    }
    return $descriptions;
}

/**
 * Legacy custom completion callback.
 *
 * @param stdClass $course Course.
 * @param stdClass $cm Course module.
 * @param int $userid User id.
 * @param bool $type Completion type.
 * @return bool
 */
function videosummary_get_completion_state($course, $cm, int $userid, bool $type): bool {
    global $DB;
    $activity = $DB->get_record('videosummary', ['id' => $cm->instance], '*', MUST_EXIST);
    $progressok = true;
    if ((int)$activity->completionpercent > 0) {
        $percent = (float)$DB->get_field('videosummary_progress', 'percent', [
            'videosummaryid' => $activity->id,
            'userid' => $userid,
        ]);
        $progressok = $percent >= (float)$activity->completionpercent;
    }
    $summaryok = true;
    if (!empty($activity->completionsummary)) {
        $summaryok = $DB->record_exists('videosummary_submissions', [
            'videosummaryid' => $activity->id,
            'userid' => $userid,
            'status' => 'submitted',
        ]);
    }
    return $progressok && $summaryok;
}

/**
 * Renders the common tracked player for an activity and user progress record.
 *
 * @param stdClass $activity Activity.
 * @param cm_info|stdClass $cm Course module.
 * @param context_module $context Module context.
 * @param stdClass|null $progress Progress record.
 * @return string
 */
function videosummary_render_player(stdClass $activity, $cm, context_module $context, ?stdClass $progress = null): string {
    global $OUTPUT;

    $manager = new source_manager();
    $player = $manager->get_player_config($activity, $context);
    $player['poster'] = videosummary_get_poster_url($context);
    $template = $player['template'];
    unset($player['template']);
    $sourcehtml = $OUTPUT->render_from_template($template, $player);

    $segments = [];
    if ($progress && !empty($progress->segments)) {
        $decoded = json_decode((string)$progress->segments, true);
        if (is_array($decoded)) {
            $segments = $decoded;
        }
    }
    $percent = $progress ? (float)$progress->percent : 0.0;
    $lastposition = $progress ? (float)$progress->lastposition : 0.0;
    $duration = $progress ? (float)$progress->duration : 0.0;
    $timeline = [];
    if ($duration > 0) {
        foreach ($segments as $segment) {
            if (!is_array($segment) || count($segment) < 2) {
                continue;
            }
            $start = max(0.0, min($duration, (float)$segment[0]));
            $end = max($start, min($duration, (float)$segment[1]));
            if (($end - $start) < 0.01) {
                continue;
            }
            $timeline[] = [
                'start' => $start,
                'left' => sprintf('%.4F', ($start / $duration) * 100.0),
                'width' => sprintf('%.4F', (($end - $start) / $duration) * 100.0),
                'tooltip' => \mod_videosummary\summary_manager::format_time($start) . '–' .
                    \mod_videosummary\summary_manager::format_time($end),
            ];
        }
    }
    $config = [
        'cmid' => (int)$cm->id,
        'allowseek' => !empty($activity->allowseek),
        'resumeplayback' => (int)$activity->resumeplayback,
        'lastposition' => $lastposition,
        'segments' => $segments,
        'player' => $player,
    ];
    return $OUTPUT->render_from_template('mod_videosummary/player', [
        'sourcehtml' => $sourcehtml,
        'configjson' => json_encode($config, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE |
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
        'progressrounded' => (int)round($percent),
        'lastpositionformatted' => \mod_videosummary\summary_manager::format_time($lastposition),
        'timeline' => $timeline,
        'hastimeline' => !empty($timeline),
    ]);
}
