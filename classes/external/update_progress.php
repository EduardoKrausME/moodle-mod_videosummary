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
 * AJAX progress update service.
 *
 * @package   mod_videosummary
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videosummary\external;

use context_module;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use mod_videosummary\progress_manager;

/**
 * Class update_progress.
 */
class update_progress extends external_api {
    /**
     * Method execute_parameters.
     *
     * @return external_function_parameters Return value.
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module id'),
            'currentposition' => new external_value(PARAM_FLOAT, 'Current position'),
            'duration' => new external_value(PARAM_FLOAT, 'Video duration'),
            'playbackrate' => new external_value(PARAM_FLOAT, 'Playback rate'),
            'segmentstart' => new external_value(PARAM_FLOAT, 'Watched interval start'),
            'segmentend' => new external_value(PARAM_FLOAT, 'Watched interval end'),
            'sequence' => new external_value(PARAM_INT, 'Client sequence'),
            'sessionkey' => new external_value(PARAM_ALPHANUMEXT, 'Playback session key'),
            'clienttime' => new external_value(PARAM_INT, 'Client timestamp'),
            'playerstate' => new external_value(PARAM_ALPHA, 'Player state'),
        ]);
    }

    /**
     * Method execute.
     *
     * @param int $cmid Parameter cmid.
     * @param float $currentposition Parameter currentposition.
     * @param float $duration Parameter duration.
     * @param float $playbackrate Parameter playbackrate.
     * @param float $segmentstart Parameter segmentstart.
     * @param float $segmentend Parameter segmentend.
     * @param int $sequence Parameter sequence.
     * @param string $sessionkey Parameter sessionkey.
     * @param int $clienttime Parameter clienttime.
     * @param string $playerstate Parameter playerstate.
     * @return array Return value.
     */
    public static function execute(int $cmid, float $currentposition, float $duration, float $playbackrate,
                                   float $segmentstart, float $segmentend, int $sequence, string $sessionkey,
                                   int $clienttime, string $playerstate): array {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'cmid' => $cmid,
            'currentposition' => $currentposition,
            'duration' => $duration,
            'playbackrate' => $playbackrate,
            'segmentstart' => $segmentstart,
            'segmentend' => $segmentend,
            'sequence' => $sequence,
            'sessionkey' => $sessionkey,
            'clienttime' => $clienttime,
            'playerstate' => $playerstate,
        ]);

        $cm = get_coursemodule_from_id('videosummary', $params['cmid'], 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/videosummary:view', $context);
        $activity = $DB->get_record('videosummary', ['id' => $cm->instance], '*', MUST_EXIST);

        $record = (new progress_manager())->update(
            $activity,
            $USER->id,
            $params['duration'],
            $params['currentposition'],
            $params['segmentstart'],
            $params['segmentend'],
            $params['playbackrate']
        );

        $completion = new \completion_info(get_course($cm->course));
        if ($completion->is_enabled($cm)) {
            $completion->update_state($cm, COMPLETION_UNKNOWN, $USER->id);
        }

        return [
            'percent' => (float)$record->percent,
            'uniquewatched' => (float)$record->uniquewatched,
            'lastposition' => (float)$record->lastposition,
            'segments' => (string)$record->segments,
            'completed' => (bool)$record->completed,
        ];
    }

    /**
     * Method execute_returns.
     *
     * @return external_single_structure Return value.
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'percent' => new external_value(PARAM_FLOAT, 'Watched percentage'),
            'uniquewatched' => new external_value(PARAM_FLOAT, 'Unique watched seconds'),
            'lastposition' => new external_value(PARAM_FLOAT, 'Last player position'),
            'segments' => new external_value(PARAM_RAW, 'Merged watched segments JSON'),
            'completed' => new external_value(PARAM_BOOL, 'Whether video percentage is complete'),
        ]);
    }
}
