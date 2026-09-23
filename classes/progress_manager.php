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
 * Server-side watched interval tracking.
 *
 * @package   mod_videosummary
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videosummary;

use stdClass;

/**
 * Class progress_manager.
 */
class progress_manager {
    /**
     * Applies one watched interval and returns the authoritative progress record.
     *
     * @param stdClass $activity Activity.
     * @param int $userid User id.
     * @param float $duration Player duration.
     * @param float $position Current position.
     * @param float $start Segment start.
     * @param float $end Segment end.
     * @param float $playbackrate Playback rate.
     * @return stdClass
     */
    public function update(stdClass $activity, int $userid, float $duration, float $position,
                           float $start, float $end, float $playbackrate = 1.0): stdClass {
        global $DB;

        $now = time();
        $duration = max(0.0, min(86400.0, $duration));
        $position = max(0.0, $duration > 0 ? min($duration, $position) : $position);
        $start = max(0.0, $duration > 0 ? min($duration, $start) : $start);
        $end = max($start, $duration > 0 ? min($duration, $end) : $end);
        $playbackrate = max(0.25, min(4.0, $playbackrate));

        // A heartbeat is expected every 10 seconds. Keep one update within a conservative maximum window.
        $maxspan = 35.0 * $playbackrate;
        if (($end - $start) > $maxspan) {
            $end = $start + $maxspan;
        }

        $record = $DB->get_record('videosummary_progress', [
            'videosummaryid' => $activity->id,
            'userid' => $userid,
        ]);
        if (!$record) {
            $record = (object)[
                'videosummaryid' => $activity->id,
                'userid' => $userid,
                'segments' => '[]',
                'duration' => $duration,
                'uniquewatched' => 0,
                'percent' => 0,
                'lastposition' => $position,
                'completed' => 0,
                'timecreated' => $now,
                'timemodified' => $now,
            ];
            $record->id = $DB->insert_record('videosummary_progress', $record);
        }

        $segments = json_decode((string)$record->segments, true);
        if (!is_array($segments)) {
            $segments = [];
        }
        if (($end - $start) >= 0.25) {
            $segments[] = [$start, $end];
        }
        $segments = self::merge_segments($segments);
        $uniquewatched = self::sum_segments($segments);
        $duration = max($duration, (float)$record->duration);
        $percent = $duration > 0 ? min(100.0, ($uniquewatched / $duration) * 100.0) : 0.0;
        $completed = (int)$activity->completionpercent <= 0 || $percent >= (float)$activity->completionpercent;

        $record->segments = json_encode($segments, JSON_UNESCAPED_SLASHES);
        $record->duration = $duration;
        $record->uniquewatched = $uniquewatched;
        $record->percent = $percent;
        $record->lastposition = $position;
        $record->completed = $completed ? 1 : 0;
        $record->timemodified = $now;
        $DB->update_record('videosummary_progress', $record);
        return $record;
    }

    /**
     * Merges overlapping and almost-touching intervals.
     *
     * @param array $segments Segments.
     * @return array
     */
    public static function merge_segments(array $segments): array {
        $valid = [];
        foreach ($segments as $segment) {
            if (!is_array($segment) || count($segment) < 2) {
                continue;
            }
            $start = max(0.0, (float)$segment[0]);
            $end = max($start, (float)$segment[1]);
            if (($end - $start) >= 0.01) {
                $valid[] = [$start, $end];
            }
        }
        usort($valid, static fn(array $a, array $b): int => $a[0] <=> $b[0]);
        $merged = [];
        foreach ($valid as $segment) {
            if (!$merged) {
                $merged[] = $segment;
                continue;
            }
            $last = count($merged) - 1;
            if ($segment[0] <= $merged[$last][1] + 0.75) {
                $merged[$last][1] = max($merged[$last][1], $segment[1]);
            } else {
                $merged[] = $segment;
            }
        }
        return $merged;
    }

    /**
     * Sums interval lengths.
     *
     * @param array $segments Segments.
     * @return float
     */
    public static function sum_segments(array $segments): float {
        $sum = 0.0;
        foreach ($segments as $segment) {
            $sum += max(0.0, (float)$segment[1] - (float)$segment[0]);
        }
        return $sum;
    }
}
