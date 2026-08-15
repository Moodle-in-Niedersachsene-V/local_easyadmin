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
 * Kurse-Unterseite: Kurse archivieren (Schuljahreswechsel).
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

$returnurl = new moodle_url('/local/easyadmin/coursearchive.php');
$dashboardurl = new moodle_url('/local/easyadmin/index.php');
$coursesurl = new moodle_url('/local/easyadmin/courses.php');

$PAGE->set_url($returnurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('archiveheader', 'local_easyadmin'));
$PAGE->set_heading(get_string('archiveheader', 'local_easyadmin'));
$PAGE->navbar->add(get_string('dashboardtitle', 'local_easyadmin'), $dashboardurl);
$PAGE->navbar->add(get_string('tile_courses_title', 'local_easyadmin'), $coursesurl);
$PAGE->navbar->add(get_string('archiveheader', 'local_easyadmin'));

// -----------------------------------------------------------------------
// Kurse archivieren: dreistufiger Ablauf (Kategorie wählen -> Kurse
// auswählen -> Bestätigen), damit eine potenziell viele Kurse betreffende
// Aktion nie ohne ausdrückliche Bestätigung ausgeführt wird.
$archivestep = optional_param('archivestep', 0, PARAM_INT);
$sourcecategoryid = optional_param('sourcecategoryid', 0, PARAM_INT);
$selectedcourseids = optional_param_array('courseids', [], PARAM_INT);
$targetcategoryname = optional_param('targetcategoryname', '', PARAM_TEXT);

// Schritt 3: tatsächliche Ausführung nach Bestätigung.
if ($archivestep === 3 && optional_param('confirmarchive', 0, PARAM_BOOL)) {
    require_sesskey();

    if (empty($selectedcourseids)) {
        redirect(
            $returnurl,
            get_string('error_nocoursesselected', 'local_easyadmin'),
            null,
            \core\output\notification::NOTIFY_ERROR
        );
    }

    $targetcategoryname = trim($targetcategoryname) !== '' ? trim($targetcategoryname)
        : get_string('archive_defaultcategoryname', 'local_easyadmin');

    $targetcategory = $DB->get_record('course_categories', ['name' => $targetcategoryname]);
    if (!$targetcategory) {
        $newcat = new stdClass();
        $newcat->name = $targetcategoryname;
        $newcat->parent = 0;
        $targetcategory = \core_course_category::create($newcat);
    } else {
        $targetcategory = \core_course_category::get($targetcategory->id);
    }

    // Kurse ausblenden: über update_course(), Moodles offizielle Funktion
    // für reguläre Kurse (nicht zu verwechseln mit dem Sonderfall SITEID).
    foreach ($selectedcourseids as $courseid) {
        $courseupdate = new stdClass();
        $courseupdate->id = $courseid;
        $courseupdate->visible = 0;
        update_course($courseupdate);
    }

    // Kurse in die Archiv-Kategorie verschieben: Moodles offizielle
    // Funktion, die auch Kontext-/Cache-Aktualisierung korrekt übernimmt.
    move_courses($selectedcourseids, $targetcategory->id);

    redirect(
        $returnurl,
        get_string('archived_success', 'local_easyadmin', count($selectedcourseids)),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('archiveheader', 'local_easyadmin'));
echo html_writer::tag('p', get_string('archiveintro', 'local_easyadmin'), ['class' => 'text-muted']);

if ($archivestep === 3 && !empty($selectedcourseids)) {
    // Schritt 3: Bestätigung.
    [$insql, $inparams] = $DB->get_in_or_equal($selectedcourseids);
    $courses = $DB->get_records_select('course', "id $insql", $inparams, 'fullname ASC', 'id, fullname');

    echo html_writer::div(get_string('archive_confirm_intro', 'local_easyadmin'), 'alert alert-warning');
    echo html_writer::start_tag('ul');
    foreach ($courses as $course) {
        echo html_writer::tag('li', format_string($course->fullname));
    }
    echo html_writer::end_tag('ul');

    echo html_writer::start_tag('form', ['method' => 'post', 'action' => $returnurl->out(false)]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'archivestep', 'value' => 3]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'confirmarchive', 'value' => 1]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'targetcategoryname', 'value' => s($targetcategoryname)]);
    foreach ($selectedcourseids as $courseid) {
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'courseids[]', 'value' => $courseid]);
    }
    echo html_writer::tag('button', get_string('archive_confirm_button', 'local_easyadmin'), [
        'type' => 'submit', 'class' => 'btn btn-danger mr-2',
    ]);
    echo html_writer::link($returnurl, get_string('archive_cancel_button', 'local_easyadmin'), ['class' => 'btn btn-secondary']);
    echo html_writer::end_tag('form');
} else if ($archivestep >= 2 && $sourcecategoryid > 0) {
    // Schritt 2: Kurse der gewählten Kategorie auswählen.
    $courses = $DB->get_records('course', ['category' => $sourcecategoryid], 'fullname ASC', 'id, fullname, visible');

    if (empty($courses)) {
        echo html_writer::div(get_string('archive_nocoursesincategory', 'local_easyadmin'), 'alert alert-info');
        echo html_writer::link(
            $returnurl,
            get_string('archive_cancel_button', 'local_easyadmin'),
            ['class' => 'btn btn-secondary']
        );
    } else {
        echo html_writer::start_tag('form', ['method' => 'post', 'action' => $returnurl->out(false)]);
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'archivestep', 'value' => 3]);

        echo html_writer::start_div('form-check mb-2 font-weight-bold');
        echo html_writer::empty_tag('input', [
            'type' => 'checkbox', 'id' => 'selectall_archive', 'checked' => 'checked', 'class' => 'form-check-input',
        ]);
        echo html_writer::tag('label', get_string('selectall_easyadmin', 'local_easyadmin'), [
            'for' => 'selectall_archive', 'class' => 'form-check-label',
        ]);
        echo html_writer::end_div();

        foreach ($courses as $course) {
            $checkboxid = 'course_' . $course->id;
            echo html_writer::start_div('form-check');
            echo html_writer::empty_tag('input', [
                'type' => 'checkbox', 'name' => 'courseids[]', 'value' => $course->id,
                'id' => $checkboxid, 'checked' => 'checked', 'class' => 'form-check-input',
            ]);
            $labeltext = format_string($course->fullname)
                . ($course->visible ? '' : ' ' . get_string('archive_alreadyhidden', 'local_easyadmin'));
            echo html_writer::tag(
                'label',
                $labeltext,
                ['for' => $checkboxid, 'class' => 'form-check-label']
            );
            echo html_writer::end_div();
        }

        echo html_writer::start_div('mt-3');
        echo html_writer::label(
            get_string('archive_targetcategoryname_easyadmin', 'local_easyadmin'),
            'targetcategoryname'
        );
        echo html_writer::empty_tag('input', [
            'type' => 'text', 'name' => 'targetcategoryname', 'id' => 'targetcategoryname',
            'value' => get_string('archive_defaultcategoryname', 'local_easyadmin'), 'class' => 'form-control',
        ]);
        echo html_writer::end_div();

        echo html_writer::tag('button', get_string('archive_continue_button', 'local_easyadmin'), [
            'type' => 'submit', 'class' => 'btn btn-primary mt-3 mr-2',
        ]);
        echo html_writer::link(
            $returnurl,
            get_string('archive_cancel_button', 'local_easyadmin'),
            ['class' => 'btn btn-secondary mt-3']
        );
        echo html_writer::end_tag('form');

        echo html_writer::script("
            document.getElementById('selectall_archive').addEventListener('change', function() {
                var checked = this.checked;
                document.querySelectorAll('input[name=\"courseids[]\"]').forEach(function(cb) {
                    cb.checked = checked;
                });
            });
        ");
    }
} else {
    // Schritt 1: Kategorie wählen.
    $categoryoptions = \core_course_category::make_categories_list('moodle/category:manage');

    if (empty($categoryoptions)) {
        echo html_writer::div(get_string('archive_nocategories', 'local_easyadmin'), 'alert alert-info');
    } else {
        echo html_writer::start_tag('form', ['method' => 'get', 'action' => $returnurl->out(false)]);
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'archivestep', 'value' => 2]);
        echo html_writer::select(
            $categoryoptions,
            'sourcecategoryid',
            '',
            false,
            ['class' => 'form-control d-inline-block w-auto']
        );
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
