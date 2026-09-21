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
 * Timestamp reference editor.
 *
 * @module mod_videosummary/reference_editor
 * @package   mod_videosummary
 * @copyright 2026 Eduardo Kraus
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['core/str'], function (Str) {
    let currentTime = 0;

    const formatTime = (seconds) => {
        const value = Math.max(0, Math.round(Number(seconds || 0)));
        const hours = Math.floor(value / 3600);
        const minutes = Math.floor((value % 3600) / 60);
        const remaining = value % 60;
        return (hours ? String(hours).padStart(2, '0') + ':' : '') +
            String(minutes).padStart(2, '0') + ':' + String(remaining).padStart(2, '0');
    };

    const input = (type, className, value) => {
        const element = document.createElement(type === 'textarea' ? 'textarea' : 'input');
        if (type !== 'textarea') {
            element.type = 'text';
        }
        element.className = className;
        element.value = value || '';
        return element;
    };

    const makeRow = (root, data, strings) => {
        const row = document.createElement('div');
        row.className = 'videosummary-reference-row border rounded p-3 mb-3';

        const timing = document.createElement('div');
        timing.className = 'd-flex flex-wrap gap-2 align-items-end';

        const makeField = (labelText, control) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'form-group mb-2';
            const label = document.createElement('label');
            label.textContent = labelText;
            wrapper.appendChild(label);
            wrapper.appendChild(control);
            return wrapper;
        };

        const start = input('input', 'form-control videosummary-ref-start', data.start || '');
        const end = input('input', 'form-control videosummary-ref-end', data.end || '');
        const label = input('input', 'form-control videosummary-ref-label', data.label || '');
        const note = input('textarea', 'form-control videosummary-ref-note', data.note || '');
        note.rows = 2;

        timing.appendChild(makeField(strings.start, start));
        timing.appendChild(makeField(strings.end, end));

        const nowStart = document.createElement('button');
        nowStart.type = 'button';
        nowStart.className = 'btn btn-sm btn-outline-secondary mb-2';
        nowStart.textContent = strings.current;
        nowStart.addEventListener('click', () => {
            start.value = formatTime(currentTime);
            sync(root);
        });
        timing.appendChild(nowStart);

        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'btn btn-sm btn-outline-danger mb-2';
        remove.textContent = strings.remove;
        remove.addEventListener('click', () => {
            row.remove();
            sync(root);
        });
        timing.appendChild(remove);

        row.appendChild(timing);
        row.appendChild(makeField(strings.label, label));
        row.appendChild(makeField(strings.note, note));
        [start, end, label, note].forEach((field) => field.addEventListener('input', () => sync(root)));
        root.querySelector('[data-region="reference-list"]').appendChild(row);
    };

    const sync = (root) => {
        const values = [];
        root.querySelectorAll('.videosummary-reference-row').forEach((row) => {
            values.push({
                start: row.querySelector('.videosummary-ref-start').value,
                end: row.querySelector('.videosummary-ref-end').value,
                label: row.querySelector('.videosummary-ref-label').value,
                note: row.querySelector('.videosummary-ref-note').value
            });
        });
        const form = root.closest('form');
        const hidden = form ? form.querySelector('input[name="referencesjson"]') : null;
        if (hidden) {
            hidden.value = JSON.stringify(values);
        }
    };

    const init = () => {
        document.addEventListener('videosummary:timeupdate', (event) => {
            currentTime = Number(event.detail && event.detail.time || 0);
        });
        Promise.all([
            Str.get_string('referencestart', 'videosummary'),
            Str.get_string('referenceend', 'videosummary'),
            Str.get_string('referencelabel', 'videosummary'),
            Str.get_string('referencenote', 'videosummary'),
            Str.get_string('removereference', 'videosummary'),
            Str.get_string('referencecurrent', 'videosummary')
        ]).then((items) => {
            const strings = {
                start: items[0],
                end: items[1],
                label: items[2],
                note: items[3],
                remove: items[4],
                current: items[5]
            };
            document.querySelectorAll('[data-region="reference-editor"]').forEach((root) => {
                let existing = [];
                const form = root.closest('form');
                const hidden = form ? form.querySelector('input[name="referencesjson"]') : null;
                const source = hidden && hidden.value ? hidden.value : (root.dataset.existing || '[]');
                try {
                    existing = JSON.parse(source);
                } catch (error) {
                    existing = [];
                }
                existing.forEach((item) => makeRow(root, item, strings));
                root.querySelector('[data-action="add-reference"]').addEventListener('click', () => {
                    makeRow(root, {start: formatTime(currentTime), end: '', label: '', note: ''}, strings);
                    sync(root);
                });
                sync(root);
            });
        });
    };

    return {init: init};
});
