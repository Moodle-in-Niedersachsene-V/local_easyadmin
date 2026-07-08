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
 * Kurse-Unterseite: Kurse sichern (herunterladbare Einzelkurs-Sicherungen).
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');

require_login();

$context = context_system::instance();
require_capability('local/easyadmin:managecourses', $context);

$returnurl = new moodle_url('/local/easyadmin/coursebackup.php');
$dashboardurl = new moodle_url('/local/easyadmin/index.php');
$coursesurl = new moodle_url('/local/easyadmin/courses.php');

$PAGE->set_url($returnurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('backupheader', 'local_easyadmin'));
$PAGE->set_heading(get_string('backupheader', 'local_easyadmin'));
$PAGE->navbar->add(get_string('dashboardtitle', 'local_easyadmin'), $dashboardurl);
$PAGE->navbar->add(get_string('tile_courses_title', 'local_easyadmin'), $coursesurl);
$PAGE->navbar->add(get_string('backupheader', 'local_easyadmin'));

// -----------------------------------------------------------------------
// Kurse sichern: Backups werden bewusst mit backup::MODE_GENERAL erzeugt
// (nicht MODE_AUTOMATED), damit sie im eigenen Dateibereich des Kurses
// landen und direkt herunterladbar sind - statt im automatisierten
// Sammelbereich in moodledata, aus dem einzelne Kurse nicht ohne
// vollständigen Server-Restore wiederhergestellt werden können.
// -----------------------------------------------------------------------
$downloadlinks = [];
$zipdownloadurl = null;
$backupstep = optional_param('backupstep', 0, PARAM_INT);
$backupcategoryid = optional_param('backupcategoryid', 0, PARAM_INT);
$backupcourseids = optional_param_array('backupcourseids', [], PARAM_INT);

if ($backupstep === 3 && optional_param('confirmbackup', 0, PARAM_BOOL)) {
    require_sesskey();

    if (empty($backupcourseids)) {
        redirect($returnurl, get_string('error_nocoursesselected', 'local_easyadmin'), null,
            \core\output\notification::NOTIFY_ERROR);
    }

    // Große Kurse können länger als das normale Skript-Zeitlimit brauchen.
    \core_php_time_limit::raise(600);
    raise_memory_limit(MEMORY_EXTRA);

    $fs = get_file_storage();
    $ownercontext = context_system::instance();
    $copiedfiles = [];

    foreach ($backupcourseids as $courseid) {
        if (!$DB->record_exists('course', ['id' => $courseid])) {
            continue;
        }

        $coursecontext = context_course::instance($courseid);

        $bc = new backup_controller(
            backup::TYPE_1COURSE,
            $courseid,
            backup::FORMAT_MOODLE,
            backup::INTERACTIVE_NO,
            backup::MODE_GENERAL,
            $USER->id
        );

        // Nutzerdaten bewusst einschließen, damit bei Bedarf ein
        // vollständiger, eigenständiger Restore möglich ist - nicht nur
        // die reine Kursstruktur.
        $plan = $bc->get_plan();
        if ($plan->setting_exists('users')) {
            $plan->get_setting('users')->set_value(true);
        }

        $bc->execute_plan();
        $bc->destroy();

        // Die soeben erzeugte Datei liegt zunächst in Moodles eigenem
        // Kurs-Sicherungsbereich (component 'backup', filearea 'course').
        // Wir kopieren sie in einen EIGENEN Dateibereich des Plugins, da wir
        // dessen Auslieferung (lib.php lokal_easyadmin_pluginfile()) selbst
        // kontrollieren und getestet haben - Moodles generische Auslieferung
        // für den Backup-Bereich funktionierte im Test nicht zuverlässig
        // ("Datei nicht gefunden").
        $sourcefiles = $fs->get_area_files($coursecontext->id, 'backup', 'course', 0, 'timecreated DESC', false);
        $sourcefile = reset($sourcefiles);

        if ($sourcefile) {
            // Moodle vergibt bei dieser Art von Sicherung oft denselben
            // generischen Dateinamen ("backup.mbz") für jeden Kurs. Wir
            // vergeben deshalb bewusst einen eindeutigen, sprechenden Namen
            // pro Kurs - sonst würde bei mehreren Kursen eine Sicherung die
            // andere überschreiben (insbesondere innerhalb der Sammel-ZIP).
            $coursedata = $DB->get_record('course', ['id' => $courseid], 'id, fullname, shortname');
            $safeshortname = clean_filename($coursedata->shortname);
            $ownfilename = sprintf('kurs_%d_%s_%s.mbz', $courseid, $safeshortname, date('Ymd_His'));

            $ownfilerecord = [
                'contextid' => $ownercontext->id,
                'component' => 'local_easyadmin',
                'filearea' => 'coursebackups',
                'itemid' => $courseid,
                'filepath' => '/',
                'filename' => $ownfilename,
            ];

            // Vorherige Sicherung(en) desselben Kurses ersetzen statt zu
            // duplizieren, falls das Formular erneut für denselben Kurs
            // genutzt wird.
            $previousfiles = $fs->get_area_files(
                $ownfilerecord['contextid'], $ownfilerecord['component'], $ownfilerecord['filearea'],
                $ownfilerecord['itemid'], 'id', false
            );
            foreach ($previousfiles as $previousfile) {
                $previousfile->delete();
            }

            $copiedfile = $fs->create_file_from_storedfile($ownfilerecord, $sourcefile);
            $copiedfiles[] = $copiedfile;

            $url = \moodle_url::make_pluginfile_url(
                $ownercontext->id,
                'local_easyadmin',
                'coursebackups',
                $courseid,
                '/',
                $copiedfile->get_filename(),
                true
            );
            $downloadlinks[] = ['coursename' => $coursedata->fullname, 'url' => $url];
        }
    }

    // Zusätzlich: Falls mehrere Kurse gesichert wurden, alle gemeinsam als
    // eine ZIP-Datei anbieten, damit nicht jede Datei einzeln angeklickt
    // werden muss.
    if (count($copiedfiles) > 1) {
        $filestozip = [];
        foreach ($copiedfiles as $file) {
            $filestozip[$file->get_filename()] = $file;
        }

        $zippacker = get_file_packer('application/zip');
        $zipfilename = 'kurssicherungen_' . date('Ymd_His') . '.zip';
        $zipitemid = time();

        $zipfile = $zippacker->archive_to_storage(
            $filestozip,
            $ownercontext->id,
            'local_easyadmin',
            'coursebackupzip',
            $zipitemid,
            '/',
            $zipfilename,
            $USER->id
        );

        if ($zipfile) {
            $zipdownloadurl = \moodle_url::make_pluginfile_url(
                $ownercontext->id,
                'local_easyadmin',
                'coursebackupzip',
                $zipitemid,
                '/',
                $zipfilename,
                true
            );
        }
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('backupheader', 'local_easyadmin'));
echo html_writer::tag('p', get_string('backupintro', 'local_easyadmin'), ['class' => 'text-muted']);
echo html_writer::tag('p', get_string('backup_retention_notice', 'local_easyadmin'), ['class' => 'text-muted']);

if (!empty($downloadlinks)) {
    echo html_writer::div(get_string('backup_done_intro', 'local_easyadmin'), 'alert alert-success');

    if ($zipdownloadurl) {
        echo html_writer::div(
            html_writer::link($zipdownloadurl, get_string('backup_downloadall_button', 'local_easyadmin'), ['class' => 'btn btn-primary']),
            'mb-3'
        );
    }

    echo html_writer::start_tag('ul');
    foreach ($downloadlinks as $entry) {
        echo html_writer::tag('li', html_writer::link(
            $entry['url'],
            get_string('backup_downloadlink', 'local_easyadmin', format_string($entry['coursename']))
        ));
    }
    echo html_writer::end_tag('ul');
    echo html_writer::tag('p', get_string('backup_downloadlink_notice', 'local_easyadmin'), ['class' => 'text-muted']);

} else if ($backupstep === 3 && !empty($backupcourseids)) {
    // --- Schritt 3: Bestätigung ---
    list($insql, $inparams) = $DB->get_in_or_equal($backupcourseids);
    $bcourses = $DB->get_records_select('course', "id $insql", $inparams, 'fullname ASC', 'id, fullname');

    echo html_writer::div(get_string('backup_confirm_intro', 'local_easyadmin'), 'alert alert-warning');
    echo html_writer::start_tag('ul');
    foreach ($bcourses as $bcourse) {
        echo html_writer::tag('li', format_string($bcourse->fullname));
    }
    echo html_writer::end_tag('ul');

    echo html_writer::start_tag('form', ['method' => 'post', 'action' => $returnurl->out(false)]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'backupstep', 'value' => 3]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'confirmbackup', 'value' => 1]);
    foreach ($backupcourseids as $courseid) {
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'backupcourseids[]', 'value' => $courseid]);
    }
    echo html_writer::tag('button', get_string('backup_confirm_button', 'local_easyadmin'), [
        'type' => 'submit', 'class' => 'btn btn-primary mr-2',
    ]);
    echo html_writer::link($returnurl, get_string('archive_cancel_button', 'local_easyadmin'), ['class' => 'btn btn-secondary']);
    echo html_writer::end_tag('form');

} else if ($backupstep >= 2 && $backupcategoryid > 0) {
    // --- Schritt 2: Kurse der gewählten Kategorie auswählen ---
    $bcourses = $DB->get_records('course', ['category' => $backupcategoryid], 'fullname ASC', 'id, fullname');

    if (empty($bcourses)) {
        echo html_writer::div(get_string('archive_nocoursesincategory', 'local_easyadmin'), 'alert alert-info');
        echo html_writer::link($returnurl, get_string('archive_cancel_button', 'local_easyadmin'), ['class' => 'btn btn-secondary']);
    } else {
        echo html_writer::start_tag('form', ['method' => 'post', 'action' => $returnurl->out(false)]);
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'backupstep', 'value' => 3]);

        echo html_writer::start_div('form-check mb-2 font-weight-bold');
        echo html_writer::empty_tag('input', [
            'type' => 'checkbox', 'id' => 'selectall_backup', 'class' => 'form-check-input',
        ]);
        echo html_writer::tag('label', get_string('selectall_easyadmin', 'local_easyadmin'), [
            'for' => 'selectall_backup', 'class' => 'form-check-label',
        ]);
        echo html_writer::end_div();

        foreach ($bcourses as $bcourse) {
            $checkboxid = 'backupcourse_' . $bcourse->id;
            echo html_writer::start_div('form-check');
            echo html_writer::empty_tag('input', [
                'type' => 'checkbox', 'name' => 'backupcourseids[]', 'value' => $bcourse->id,
                'id' => $checkboxid, 'class' => 'form-check-input',
            ]);
            echo html_writer::tag('label', format_string($bcourse->fullname), [
                'for' => $checkboxid, 'class' => 'form-check-label',
            ]);
            echo html_writer::end_div();
        }

        echo html_writer::tag('button', get_string('archive_continue_button', 'local_easyadmin'), [
            'type' => 'submit', 'class' => 'btn btn-primary mt-3 mr-2',
        ]);
        echo html_writer::link($returnurl, get_string('archive_cancel_button', 'local_easyadmin'), ['class' => 'btn btn-secondary mt-3']);
        echo html_writer::end_tag('form');

        echo html_writer::script("
            document.getElementById('selectall_backup').addEventListener('change', function() {
                var checked = this.checked;
                document.querySelectorAll('input[name=\"backupcourseids[]\"]').forEach(function(cb) {
                    cb.checked = checked;
                });
            });
        ");
    }

} else {
    // --- Schritt 1: Kategorie wählen ---
    $categoryoptions = \core_course_category::make_categories_list('moodle/category:manage');

    if (empty($categoryoptions)) {
        echo html_writer::div(get_string('archive_nocategories', 'local_easyadmin'), 'alert alert-info');
    } else {
        echo html_writer::start_tag('form', ['method' => 'get', 'action' => $returnurl->out(false)]);
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'backupstep', 'value' => 2]);
        echo html_writer::select($categoryoptions, 'backupcategoryid', '', false, ['class' => 'form-control d-inline-block w-auto']);
        echo ' ';
        echo html_writer::tag('button', get_string('archive_choosecategory_button', 'local_easyadmin'), [
            'type' => 'submit', 'class' => 'btn btn-secondary',
        ]);
        echo html_writer::end_tag('form');
    }
}

echo html_writer::div(
    html_writer::link($coursesurl, get_string('backtocourses_easyadmin', 'local_easyadmin')),
    'mt-3'
);

echo $OUTPUT->footer();
