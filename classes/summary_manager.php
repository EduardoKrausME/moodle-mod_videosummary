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
 * Summary text, criteria and timestamp reference utilities.
 *
 * @package   mod_videosummary
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videosummary;

/**
 * Class summary_manager.
 */
class summary_manager {
    /**
     * Method plain_text.
     *
     * @param string $html Parameter html.
     * @return string Return value.
     */
    public static function plain_text(string $html): string {
        return trim(html_to_text($html, 0, false));
    }

    /**
     * Method word_count.
     *
     * @param string $html Parameter html.
     * @return int Return value.
     */
    public static function word_count(string $html): int {
        $text = self::plain_text($html);
        if ($text === '') {
            return 0;
        }
        preg_match_all('/[\p{L}\p{N}]+(?:[\'’_-][\p{L}\p{N}]+)*/u', $text, $matches);
        return count($matches[0]);
    }

    /**
     * Method char_count.
     *
     * @param string $html Parameter html.
     * @return int Return value.
     */
    public static function char_count(string $html): int {
        $text = self::plain_text($html);
        return function_exists('mb_strlen') ? mb_strlen($text) : strlen($text);
    }

    /**
     * Method criteria.
     *
     * @param string $raw Parameter raw.
     * @return array Return value.
     */
    public static function criteria(string $raw): array {
        $lines = preg_split('/\R/u', $raw) ?: [];
        $out = [];
        foreach ($lines as $line) {
            $line = trim(clean_param($line, PARAM_TEXT));
            if ($line !== '' && !in_array($line, $out, true)) {
                $out[] = $line;
            }
            if (count($out) >= 20) {
                break;
            }
        }
        return $out;
    }

    /**
     * Method parse_timecode.
     *
     * @param string $value Parameter value.
     * @return ?float Return value.
     */
    public static function parse_timecode(string $value): ?float {
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        if (is_numeric($value)) {
            return max(0.0, (float)$value);
        }
        if (!preg_match('/^(?:(\d+):)?(\d{1,2}):(\d{2})(?:\.(\d{1,3}))?$/', $value, $m)) {
            return null;
        }
        $hours = isset($m[1]) && $m[1] !== '' ? (int)$m[1] : 0;
        $minutes = (int)$m[2];
        $seconds = (int)$m[3];
        if ($minutes > 59 && $hours > 0 || $seconds > 59) {
            return null;
        }
        $fraction = !empty($m[4]) ? (float)('0.' . $m[4]) : 0.0;
        return ($hours * 3600) + ($minutes * 60) + $seconds + $fraction;
    }

    /**
     * Method format_time.
     *
     * @param float $seconds Parameter seconds.
     * @return string Return value.
     */
    public static function format_time(float $seconds): string {
        $seconds = max(0, (int)round($seconds));
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $remaining = $seconds % 60;
        return $hours > 0
            ? sprintf('%02d:%02d:%02d', $hours, $minutes, $remaining)
            : sprintf('%02d:%02d', $minutes, $remaining);
    }

    /**
     * Method parse_references.
     *
     * @param string $json Parameter json.
     * @return ?array Return value.
     */
    public static function parse_references(string $json): ?array {
        if (trim($json) === '') {
            return [];
        }
        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            return null;
        }
        $out = [];
        foreach ($decoded as $item) {
            if (!is_array($item)) {
                return null;
            }
            $start = self::parse_timecode((string)($item['start'] ?? ''));
            $endraw = trim((string)($item['end'] ?? ''));
            $end = $endraw === '' ? $start : self::parse_timecode($endraw);
            $label = trim(clean_param((string)($item['label'] ?? ''), PARAM_TEXT));
            $note = trim(clean_param((string)($item['note'] ?? ''), PARAM_TEXT));
            if ($start === null || $end === null || $end < $start) {
                return null;
            }
            if ($label === '' && $note === '') {
                $label = self::format_time($start);
            }
            $out[] = [
                'starttime' => $start,
                'endtime' => $end,
                'label' => $label,
                'note' => $note,
            ];
            if (count($out) >= 100) {
                break;
            }
        }
        return $out;
    }

    /**
     * Method validate_final.
     *
     * @param \stdClass $activity Parameter activity.
     * @param string $html Parameter html.
     * @param array $references Parameter references.
     * @return array Return value.
     */
    public static function validate_final(\stdClass $activity, string $html, array $references): array {
        $errors = [];
        $wordcount = self::word_count($html);
        $charcount = self::char_count($html);
        if ((int)$activity->wordlimit > 0 && $wordcount > (int)$activity->wordlimit) {
            $errors['summaryeditor'] = get_string('wordlimiterror', 'videosummary', (object)[
                'count' => $wordcount,
                'limit' => (int)$activity->wordlimit,
            ]);
        }
        if ((int)$activity->charlimit > 0 && $charcount > (int)$activity->charlimit) {
            $errors['summaryeditor'] = get_string('charlimiterror', 'videosummary', (object)[
                'count' => $charcount,
                'limit' => (int)$activity->charlimit,
            ]);
        }
        if (count($references) < (int)$activity->minreferences) {
            $errors['referencesjson'] = get_string('minreferenceserror', 'videosummary', (int)$activity->minreferences);
        }
        $text = self::plain_text($html);
        $lines = array_values(array_filter(array_map('trim', preg_split('/\R/u', $text) ?: [])));
        if ($activity->summaryformat === 'fivepoints' && count($lines) < 5) {
            $errors['summaryeditor'] = get_string('fivepointserror', 'videosummary');
        }
        if ($activity->summaryformat === 'threeconcepts' && count($lines) < 3) {
            $errors['summaryeditor'] = get_string('threeconceptserror', 'videosummary');
        }
        return $errors;
    }

    /**
     * Method instruction_key.
     *
     * @param string $format Parameter format.
     * @return string Return value.
     */
    public static function instruction_key(string $format): string {
        $allowed = ['free', 'maxwords', 'fivepoints', 'threeconcepts', 'conclusion', 'chapters'];
        if (!in_array($format, $allowed, true)) {
            $format = 'free';
        }
        return 'instruction_' . $format;
    }
}
