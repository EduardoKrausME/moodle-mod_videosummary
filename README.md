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

Video Summary requires **Moodle 4.5 or later** because it depends on [Moodle Video Bridge (`local_video_bridge`)](https://github.com/EduardoKrausME/moodle-local_video_bridge). Video Bridge provides the required video sources, playback integration and tracking, and must be installed in `local/video_bridge` before installing or upgrading this activity. Video Bridge currently also requires `local_ai_bridge`.

**Important:** `mod_videosummary/version.php` does not currently declare `local_video_bridge` in `$plugin->dependencies`. Moodle may therefore not detect a missing Video Bridge automatically; administrators must verify that it is installed and compatible.

## Installation

Install Video Bridge and its dependencies first, then install `mod_videosummary` in `mod/videosummary` and visit **Site administration > Notifications**.

Existing protected uploads are copied on first access from the legacy Video Summary file area to the Video Bridge file area, leaving the originals intact until the activity is updated or deleted. Backups retain both file areas for compatibility.

An installation limited to plugins available on the Moodle Marketplace must ensure that these dependencies are available there, as Video Summary cannot run without Video Bridge.

## License

GNU GPL v3 or later.
