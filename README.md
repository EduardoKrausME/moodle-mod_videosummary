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

Video Summary requires **Moodle 4.5 or later** and [Moodle Video Bridge (`local_video_bridge`)](https://github.com/EduardoKrausME/moodle-local_video_bridge) for sources, playback and tracking. Video Bridge also depends on `local_ai_bridge`.

The activity declares `local_video_bridge` as a required dependency in `version.php`.

## License

GNU GPL v3 or later.
