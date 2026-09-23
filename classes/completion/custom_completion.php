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
 * Custom completion rules.
 *
 * @package   mod_videosummary
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videosummary\completion;

use core_completion\activity_custom_completion;

/**
 * Class custom_completion.
 */
class custom_completion extends activity_custom_completion {
    /**
     * Method get_state.
     *
     * @param string $rule Parameter rule.
     * @return int Return value.
     */
    public function get_state(string $rule): int {
        global $DB;

        $activity = $DB->get_record('videosummary', ['id' => $this->cm->instance], '*', MUST_EXIST);

        if ($rule === 'completionpercent') {
            if ((int)$activity->completionpercent <= 0) {
                return COMPLETION_COMPLETE;
            }
            $percent = (float)$DB->get_field('videosummary_progress', 'percent', [
                'videosummaryid' => $activity->id,
                'userid' => $this->userid,
            ]);
            return $percent >= (float)$activity->completionpercent ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
        }
        if ($rule === 'completionsummary') {
            if (empty($activity->completionsummary)) {
                return COMPLETION_COMPLETE;
            }
            return $DB->record_exists('videosummary_submissions', [
                'videosummaryid' => $activity->id,
                'userid' => $this->userid,
                'status' => 'submitted',
            ]) ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
        }
        return COMPLETION_INCOMPLETE;
    }

    /**
     * Method get_defined_custom_rules.
     *
     * @return array Return value.
     */
    public static function get_defined_custom_rules(): array {
        return ['completionpercent', 'completionsummary'];
    }

    /**
     * Method get_custom_rule_descriptions.
     *
     * @return array Return value.
     */
    public function get_custom_rule_descriptions(): array {
        global $DB;
        $activity = $DB->get_record('videosummary', ['id' => $this->cm->instance], '*', MUST_EXIST);
        return [
            'completionpercent' => get_string('completiondetail:percent', 'videosummary', $activity->completionpercent),
            'completionsummary' => get_string('completiondetail:summary', 'videosummary'),
        ];
    }

    /**
     * Method get_sort_order.
     *
     * @return array Return value.
     */
    public function get_sort_order(): array {
        return ['completionpercent', 'completionsummary'];
    }
}
