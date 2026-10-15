# Video Summary

`mod_videosummary` is a Moodle activity for synthesis and comprehension through video-based summaries.

Students watch a tracked video, resume from their last position, save a draft, submit a final summary, and relate parts
of the summary to exact moments or intervals in the video. Teachers can require a minimum number of video references,
set word/character limits, choose a summary structure, grade using simple criteria, and review a course report
containing watched percentage, draft/submission state, references, grade, and status.

Video sources are supplied by the required `local_video_bridge` plugin and its `videoprogresssource_*` adapters.
Only adapters with reliable tracking and seeking are offered because summary completion and timestamp references
require these capabilities. Providers and their playback assets are no longer bundled in this activity.

## Main features

- Real watched-segment tracking and resumable playback.
- Optional seek restriction to previously watched segments.
- Free summary, maximum-word summary, five key points, three concepts, conclusion, and chapter-based formats.
- Draft and final submission workflow.
- Timestamp/interval references with click-to-seek navigation.
- Minimum reference count, word limit, and character limit.
- Configurable simple grading criteria and Moodle gradebook integration.
- Activity completion by watched percentage and/or final submission.
- Teacher report and per-submission grading page.
- Moodle privacy API and backup/restore support.
- English and Brazilian Portuguese language packs.

## Requirements

Moodle 4.4 or later (2024042200), and the `local_video_bridge` plugin with its source subplugins.

## Installation

Install `local_video_bridge` at `local/video_bridge` before installing or upgrading `mod/videosummary`,
then visit Site administration > Notifications. Existing protected uploads are copied on first access
from the legacy Video Summary file area to the Video Bridge file area, leaving the originals intact
until the activity is updated or deleted. Backups retain both file areas for compatibility.

## License

GNU GPL v3 or later.
