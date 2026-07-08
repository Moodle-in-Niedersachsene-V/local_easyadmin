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
 * Kurse-Unterseite: Kurse endgültig löschen.
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/course/lib.php');

require_login();

$context = context_system::instance();
require_capability('local/easyadmin:managecourses', $context);

$returnurl = new moodle_url('/local/easyadmin/coursedelete.php');
$dashboardurl = new moodle_url('/local/easyadmin/index.php');
$coursesurl = new moodle_url('/local/easyadmin/courses.php');

$PAGE->set_url($returnurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('deleteheader', 'local_easyadmin'));
$PAGE->set_heading(get_string('deleteheader', 'local_easyadmin'));
$PAGE->navbar->add(get_string('dashboardtitle', 'local_easyadmin'), $dashboardurl);
$PAGE->navbar->add(get_string('tile_courses_title', 'local_easyadmin'), $coursesurl);
$PAGE->navbar->add(get_string('deleteheader', 'local_easyadmin'));

// -----------------------------------------------------------------------
// Kurse löschen: dreistufiger Ablauf wie beim Archivieren/Sichern, jedoch
// mit einer zusätzlichen Sicherheitsstufe (Pflicht-Häkchen "Ich bin mir
// bewusst..."), da diese Aktion - anders als Archivieren/Sichern -
// UNWIDERRUFLICH ist und Kurse samt aller Inhalte endgültig entfernt.
// -----------------------------------------------------------------------
$deletestep = optional_param('deletestep', 0, PARAM_INT);
$deletecategoryid = optional_param('deletecategoryid', 0, PARAM_INT);
$deletecourseids = optional_param_array('deletecourseids', [], PARAM_INT);
$deletedcoursenames = [];

if ($deletestep === 3 && optional_param('confirmdelete', 0, PARAM_BOOL)) {
    require_sesskey();

    if (empty(optional_param('deleteacknowledge', 0, PARAM_BOOL))) {
        redirect($returnurl, get_string('error_deletenotacknowledged', 'local_easyadmin'), null,
            \core\output\notification::NOTIFY_ERROR);
    }

    if (empty($deletecourseids)) {
        redirect($returnurl, get_string('error_nocoursesselected', 'local_easyadmin'), null,
            \core\output\notification::NOTIFY_ERROR);
    }

    foreach ($deletecourseids as $courseid) {
        $courserecord = $DB->get_record('course', ['id' => $courseid]);
        if (!$courserecord || $courserecord->id == SITEID) {
            // SITEID (Startseite) kann und darf hierüber nie gelöscht werden.
            continue;
        }

        delete_course($courserecord, false);
        $deletedcoursenames[] = $courserecord->fullname;
    }

    redirect(
        $returnurl,
        get_string('deleted_success_easyadmin', 'local_easyadmin', count($deletedcoursenames)),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('deleteheader', 'local_easyadmin'));
echo html_writer::div(get_string('deleteintro', 'local_easyadmin'), 'alert alert-danger');

if ($deletestep === 3 && !empty($deletecourseids)) {
    // --- Schritt 3: Bestätigung mit Pflicht-Häkchen ---
    list($insql, $inparams) = $DB->get_in_or_equal($deletecourseids);
    $dcourses = $DB->get_records_select('course', "id $insql", $inparams, 'fullname ASC', 'id, fullname');

    echo html_writer::div(get_string('delete_confirm_intro', 'local_easyadmin'), 'alert alert-warning');
    echo html_writer::start_tag('ul');
    foreach ($dcourses as $dcourse) {
        echo html_writer::tag('li', format_string($dcourse->fullname));
    }
    echo html_writer::end_tag('ul');

    echo html_writer::start_tag('form', ['method' => 'post', 'action' => $returnurl->out(false)]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'deletestep', 'value' => 3]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'confirmdelete', 'value' => 1]);
    foreach ($deletecourseids as $courseid) {
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'deletecourseids[]', 'value' => $courseid]);
    }

    echo html_writer::start_div('form-check mb-3');
    echo html_writer::empty_tag('input', [
        'type' => 'checkbox', 'name' => 'deleteacknowledge', 'value' => 1,
        'id' => 'deleteacknowledge', 'class' => 'form-check-input', 'required' => 'required',
    ]);
    echo html_writer::tag('label', get_string('delete_acknowledge_easyadmin', 'local_easyadmin'), [
        'for' => 'deleteacknowledge', 'class' => 'form-check-label font-weight-bold',
    ]);
    echo html_writer::end_div();

    echo html_writer::tag('button', get_string('delete_confirm_button', 'local_easyadmin'), [
        'type' => 'submit', 'class' => 'btn btn-danger mr-2',
    ]);
    echo html_writer::link($returnurl, get_string('archive_cancel_button', 'local_easyadmin'), ['class' => 'btn btn-secondary']);
    echo html_writer::end_tag('form');

} else if ($deletestep >= 2 && $deletecategoryid > 0) {
    // --- Schritt 2: Kurse der gewählten Kategorie auswählen ---
    $dcourses = $DB->get_records('course', ['category' => $deletecategoryid], 'fullname ASC', 'id, fullname');
    $dcourses = array_filter($dcourses, static function($c) {
        return $c->id != SITEID;
    });

    if (empty($dcourses)) {
        echo html_writer::div(get_string('archive_nocoursesincategory', 'local_easyadmin'), 'alert alert-info');
        echo html_writer::link($returnurl, get_string('archive_cancel_button', 'local_easyadmin'), ['class' => 'btn btn-secondary']);
    } else {
        echo html_writer::start_tag('form', ['method' => 'post', 'action' => $returnurl->out(false)]);
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'deletestep', 'value' => 3]);

        echo html_writer::start_div('form-check mb-2 font-weight-bold');
        echo html_writer::empty_tag('input', [
            'type' => 'checkbox', 'id' => 'selectall_delete', 'class' => 'form-check-input',
        ]);
        echo html_writer::tag('label', get_string('selectall_easyadmin', 'local_easyadmin'), [
            'for' => 'selectall_delete', 'class' => 'form-check-label',
        ]);
        echo html_writer::end_div();

        foreach ($dcourses as $dcourse) {
            $checkboxid = 'deletecourse_' . $dcourse->id;
            echo html_writer::start_div('form-check');
            echo html_writer::empty_tag('input', [
                'type' => 'checkbox', 'name' => 'deletecourseids[]', 'value' => $dcourse->id,
                'id' => $checkboxid, 'class' => 'form-check-input',
            ]);
            echo html_writer::tag('label', format_string($dcourse->fullname), [
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
            document.getElementById('selectall_delete').addEventListener('change', function() {
                var checked = this.checked;
                document.querySelectorAll('input[name=\"deletecourseids[]\"]').forEach(function(cb) {
                    cb.checked = checked;
                });
            });
        ");
    }

} else {
    // --- Schritt 1: Kategorie wählen ---
    $categoryoptions = \core_course_category::make_categories_list('moodle/course:delete');

    if (empty($categoryoptions)) {
        echo html_writer::div(get_string('archive_nocategories', 'local_easyadmin'), 'alert alert-info');
    } else {
        echo html_writer::start_tag('form', ['method' => 'get', 'action' => $returnurl->out(false)]);
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'deletestep', 'value' => 2]);
        echo html_writer::select($categoryoptions, 'deletecategoryid', '', false, ['class' => 'form-control d-inline-block w-auto']);
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
