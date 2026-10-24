# Changelog

This changelog documents only the two most recent published GitHub releases. Each entry describes verified changes relative to its immediately preceding published release. Changes made later on `master` are excluded.

## 1.0.8 - 2026-10-15

### Changed

- Added the plugin icon at `pix/icon.png` and reorganized the SVG logo asset as `pix/monologo.svg`.
- Updated the plugin release identifier from `1.0.7` to `1.0.8` and its build number from `2026101501` to `2026101502`.

No changes to Video Summary activity functionality were identified in this tag comparison.

[Compare 1.0.7...1.0.8](https://github.com/EduardoKrausME/moodle-mod_videosummary/compare/1.0.7...1.0.8)

## 1.0.7 - 2026-10-15

### Changed

- Migrated video-source integration from bundled `videosummarysource_*` subplugins (upload, direct URL, YouTube and Vimeo) to the external `local_video_bridge` source manager and its `videoprogresssource_*` adapters. The bundled source subplugins, registry and source-management classes were removed.
- Updated the player JavaScript to load and validate source-specific Video Bridge AMD adapter modules instead of implementing separate HTML5, YouTube and Vimeo adapters within Video Summary.
- Restricted available video sources in the activity settings form to adapters advertising both `tracking` and `seeking`, with a missing-source check. Video Summary retains its own progress records; player setup requests Video Bridge analytics level `LEVEL_OFF` and removes the Bridge progress configuration.
- Added compatibility handling for previously uploaded videos: `classes/source/legacy_files.php` copies existing files from `mod_videosummary/video` to `local_video_bridge/video` on access, without deleting the originals during copying. Switching an existing activity away from an upload source clears the legacy Video Summary video file area.
- Updated backup and restore to include the `local_video_bridge/video` file area alongside the original Video Summary file areas; explicitly loaded the respective backup/restore step libraries in their task classes and added Moodle internal-access guards.
- Added an upgrade step to remove retired `videosummarysource_*` entries from `config_plugins`, using an escaped SQL LIKE prefix.
- Updated the README to document the required Video Bridge plugin, its source adapters, installation order and legacy-upload compatibility behavior.
- Updated GitHub Actions CI services from PostgreSQL 16 to 17 and MariaDB 10 to 11.4, revising MariaDB startup and health-check configuration.
- Updated the plugin release identifier from `1.0.6` to `1.0.7` and its build number from `2026101200` to `2026101501`.

[Compare 1.0.6...1.0.7](https://github.com/EduardoKrausME/moodle-mod_videosummary/compare/1.0.6...1.0.7)
