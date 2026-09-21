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
 * English strings for Video Summary.
 *
 * @package   mod_videosummary
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$string['actions'] = 'Actions';
$string['addreference'] = 'Add reference';
$string['allowseek'] = 'Allow seeking to unwatched parts';
$string['backtoactivity'] = 'Back to activity';
$string['backtoreport'] = 'Back to report';
$string['charcount'] = 'Characters';
$string['charlimit'] = 'Maximum characters';
$string['charlimit_help'] = 'Use 0 for no character limit.';
$string['charlimiterror'] = 'The summary has {$a->count} characters; the maximum is {$a->limit}.';
$string['completiondetail:percent'] = 'Watch at least {$a}% of the video';
$string['completiondetail:summary'] = 'Submit the final summary';
$string['completionpercent'] = 'Required watched percentage';
$string['completionsummary'] = 'Require final summary submission';
$string['criteriascoreinvalid'] = 'Criterion scores must be between 0 and 100.';
$string['criterion'] = 'Criterion';
$string['defaultcriteria'] = 'Clarity
Synthesis
Comprehension
Use of video evidence';
$string['draftsaved'] = 'Draft saved.';
$string['editsummary'] = 'Write or edit summary';
$string['eventcoursemoduleviewed'] = 'Video Summary viewed';
$string['eventsummarygraded'] = 'Video summary graded';
$string['eventsummarysubmitted'] = 'Video summary submitted';
$string['feedback'] = 'Feedback';
$string['finalgrade'] = 'Final grade';
$string['fivepointserror'] = 'The final summary must contain at least five distinct points or lines.';
$string['format_chapters'] = 'Summary divided by chapters';
$string['format_conclusion'] = 'Conclusion';
$string['format_fivepoints'] = 'Five main points';
$string['format_free'] = 'Free summary';
$string['format_maxwords'] = 'Summary with a maximum number of words';
$string['format_threeconcepts'] = 'Three important concepts';
$string['grade'] = 'Grade';
$string['gradeaction'] = 'Grade';
$string['gradesaved'] = 'Grade saved.';
$string['grading'] = 'Grading';
$string['gradingcriteria'] = 'Simple grading criteria';
$string['gradingcriteria_help'] = 'Enter one criterion per line. Each criterion is scored from 0 to 100 and the activity grade is calculated from the average.';
$string['instruction_chapters'] = 'Organize the synthesis by video sections or chapters and use timestamp references to identify them.';
$string['instruction_conclusion'] = 'Write a conclusion that synthesizes the central message and its implications.';
$string['instruction_fivepoints'] = 'Present at least five main points. Use separate lines or bullets to make them clear.';
$string['instruction_free'] = 'Write a clear synthesis of the video in your own words.';
$string['instruction_maxwords'] = 'Write a synthesis within the configured word limit.';
$string['instruction_threeconcepts'] = 'Present at least three important concepts. Use separate lines or bullets to make them clear.';
$string['invalidplayer'] = 'The video player could not be initialized.';
$string['invalidurl'] = 'Enter a valid video URL.';
$string['lastposition'] = 'Last position';
$string['maxgrade'] = 'Maximum grade';
$string['minreferences'] = 'Minimum video references';
$string['minreferences_help'] = 'Minimum number of timestamps or video intervals required for final submission.';
$string['minreferenceserror'] = 'At least {$a} video reference(s) are required.';
$string['modulename'] = 'Video Summary';
$string['modulenameplural'] = 'Video Summaries';
$string['no'] = 'No';
$string['nosubmission'] = 'No summary submission was found.';
$string['notgraded'] = 'Not graded';
$string['pendingupdates'] = 'Some tracking updates are waiting to be synchronized.';
$string['percentwatched'] = 'Watched';
$string['playbackheader'] = 'Playback and tracking';
$string['pluginadministration'] = 'Video Summary administration';
$string['pluginname'] = 'Video Summary';
$string['poster'] = 'Poster image';
$string['privacy:metadata:cgrades'] = 'Stores scores assigned to configured grading criteria.';
$string['privacy:metadata:cgrades:criterion'] = 'The grading criterion.';
$string['privacy:metadata:cgrades:score'] = 'The score assigned to the criterion.';
$string['privacy:metadata:progress'] = 'Stores watched video progress for each student.';
$string['privacy:metadata:progress:lastposition'] = 'The last playback position used for resume.';
$string['privacy:metadata:progress:percent'] = 'The watched percentage.';
$string['privacy:metadata:progress:segments'] = 'The watched video intervals.';
$string['privacy:metadata:progress:userid'] = 'The user whose video progress is stored.';
$string['privacy:metadata:references'] = 'Stores timestamp references attached to a summary.';
$string['privacy:metadata:references:endtime'] = 'Reference end time.';
$string['privacy:metadata:references:label'] = 'Reference label.';
$string['privacy:metadata:references:note'] = 'Reference note.';
$string['privacy:metadata:references:starttime'] = 'Reference start time.';
$string['privacy:metadata:submissions'] = 'Stores student summaries, status, grades and feedback.';
$string['privacy:metadata:submissions:feedback'] = 'Teacher feedback.';
$string['privacy:metadata:submissions:grade'] = 'The grade assigned to the summary.';
$string['privacy:metadata:submissions:grader'] = 'The teacher who graded the summary.';
$string['privacy:metadata:submissions:status'] = 'Draft or final submission status.';
$string['privacy:metadata:submissions:summarytext'] = 'The summary written by the user.';
$string['privacy:metadata:submissions:userid'] = 'The user who wrote the summary.';
$string['progress'] = 'Video progress';
$string['reference'] = 'Reference';
$string['referencecurrent'] = 'Use current time';
$string['referenceend'] = 'End';
$string['referenceexample'] = 'Examples: 04:30 or 01:02:15';
$string['referenceinvalid'] = 'One or more video references are invalid.';
$string['referencelabel'] = 'Title or section';
$string['referencenote'] = 'Related summary note';
$string['references'] = 'Video references';
$string['referencescount'] = 'References';
$string['referencestart'] = 'Start';
$string['removereference'] = 'Remove reference';
$string['report'] = 'Video Summary report';
$string['resumeask'] = 'Ask the student';
$string['resumeautomatic'] = 'Resume automatically';
$string['resumefromstart'] = 'Always start from the beginning';
$string['resumeno'] = 'Start over';
$string['resumeplayback'] = 'Resume playback';
$string['resumequestion'] = 'Continue from {$a}?';
$string['resumeyes'] = 'Continue';
$string['savedraft'] = 'Save draft';
$string['savegrade'] = 'Save grade';
$string['score'] = 'Score (0-100)';
$string['seekblocked'] = 'You can only seek within parts already watched.';
$string['sourceheader'] = 'Video source';
$string['sourcepluginmissing'] = 'The video source plugin "{$a}" is not available.';
$string['status'] = 'Status';
$string['status_draft'] = 'Draft';
$string['status_graded'] = 'Graded';
$string['status_notstarted'] = 'Not started';
$string['status_submitted'] = 'Submitted';
$string['student'] = 'Student';
$string['submission'] = 'Summary';
$string['submitfinal'] = 'Submit final summary';
$string['subplugintype_videosummarysource'] = 'Video Summary source';
$string['subplugintype_videosummarysource_plural'] = 'Video Summary sources';
$string['summarydelivered'] = 'Summary submitted';
$string['summaryformat'] = 'Summary format';
$string['summaryinstructions'] = 'Summary instructions';
$string['summarylocked'] = 'This summary has already been submitted and can no longer be edited.';
$string['summarysettings'] = 'Summary settings';
$string['summarystarted'] = 'Summary started';
$string['summarysubmitted'] = 'Final summary submitted.';
$string['summarytext'] = 'Summary text';
$string['threeconceptserror'] = 'The final summary must contain at least three distinct concepts or lines.';
$string['timeline'] = 'Watched timeline';
$string['timelinehint'] = 'Watched ranges are highlighted. Select a range to return to that point.';
$string['videonotsupported'] = 'Your browser cannot play this video source.';
$string['videosource'] = 'Video source';
$string['videosummary:addinstance'] = 'Add a new Video Summary';
$string['videosummary:grade'] = 'Grade Video Summary submissions';
$string['videosummary:submit'] = 'Submit a video summary';
$string['videosummary:view'] = 'View Video Summary';
$string['videosummary:viewreport'] = 'View Video Summary report';
$string['videosummaryname'] = 'Video Summary name';
$string['viewaction'] = 'View';
$string['viewreport'] = 'View report';
$string['viewsubmission'] = 'View submission';
$string['watchedpercent'] = '{$a}% watched';
$string['wordcount'] = 'Words';
$string['wordlimit'] = 'Maximum words';
$string['wordlimit_help'] = 'Use 0 for no word limit.';
$string['wordlimiterror'] = 'The summary has {$a->count} words; the maximum is {$a->limit}.';
$string['yes'] = 'Yes';
