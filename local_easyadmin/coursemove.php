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
 * Kurse-Unterseite: Kurse in eine andere Kategorie verschieben.
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

$returnurl = new moodle_url('/local/easyadmin/coursemove.php');
$dashboardurl = new moodle_url('/local/easyadmin/index.php');
$coursesurl = new moodle_url('/local/easyadmin/courses.php');

$PAGE->set_url($returnurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('moveheader', 'local_easyadmin'));
$PAGE->set_heading(get_string('moveheader', 'local_easyadmin'));
$PAGE->navbar->add(get_string('dashboardtitle', 'local_easyadmin'), $dashboardurl);
$PAGE->navbar->add(get_string('tile_courses_title', 'local_easyadmin'), $coursesurl);
$PAGE->navbar->add(get_string('moveheader', 'local_easyadmin'));

// -----------------------------------------------------------------------
// Kurse verschieben: dreistufiger Ablauf wie bei Archivieren/Sichern/
// Löschen (Kategorie wählen -> Kurse auswählen + Zielkategorie -> Bestätigen).
// -----------------------------------------------------------------------
$movestep = optional_param('movestep', 0, PARAM_INT);
$movesourcecategoryid = optional_param('movesourcecategoryid', 0, PARAM_INT);
$movetargetcategoryid = optional_param('movetargetcategoryid', 0, PARAM_INT);
$movecourseids = optional_param_array('movecourseids', [], PARAM_INT);

if ($movestep === 3 && optional_param('confirmmove', 0, PARAM_BOOL)) {
    require_sesskey();

    if (empty($movecourseids)) {
        redirect($returnurl, get_string('error_nocoursesselected', 'local_easyadmin'), null,
            \core\output\notification::NOTIFY_ERROR);
    }

    if (empty($movetargetcategoryid) || !$DB->record_exists('course_categories', ['id' => $movetargetcategoryid])) {
        redirect($returnurl, get_string('error_notargetcategory', 'local_easyadmin'), null,
            \core\output\notification::NOTIFY_ERROR);
    }

    // Moodles offizielle Funktion für den Kategoriewechsel mehrerer Kurse
    // auf einmal, inklusive korrektem Cache-/Kontext-Update.
    move_courses($movecourseids, $movetargetcategoryid);

    redirect(
        $returnurl,
        get_string('moved_success_easyadmin', 'local_easyadmin', count($movecourseids)),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('moveheader', 'local_easyadmin'));
echo html_writer::tag('p', get_string('moveintro', 'local_easyadmin'), ['class' => 'text-muted']);

if ($movestep === 3 && !empty($movecourseids)) {
    // --- Schritt 3: Bestätigung ---
    list($insql, $inparams) = $DB->get_in_or_equal($movecourseids);
    $mcourses = $DB->get_records_select('course', "id $insql", $inparams, 'fullname ASC', 'id, fullname');
    $targetcategory = \core_course_category::get($movetargetcategoryid, IGNORE_MISSING);

    echo html_writer::div(get_string('move_confirm_intro', 'local_easyadmin', $targetcategory ? $targetcategory->get_formatted_name() : ''), 'alert alert-warning');
    echo html_writer::start_tag('ul');
    foreach ($mcourses as $mcourse) {
        echo html_writer::tag('li', format_string($mcourse->fullname));
    }
    echo html_writer::end_tag('ul');

    echo html_writer::start_tag('form', ['method' => 'post', 'action' => $returnurl->out(false)]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'movestep', 'value' => 3]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'confirmmove', 'value' => 1]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'movetargetcategoryid', 'value' => $movetargetcategoryid]);
    foreach ($movecourseids as $courseid) {
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'movecourseids[]', 'value' => $courseid]);
    }
    echo html_writer::tag('button', get_string('move_confirm_button', 'local_easyadmin'), [
        'type' => 'submit', 'class' => 'btn btn-primary mr-2',
    ]);
    echo html_writer::link($returnurl, get_string('archive_cancel_button', 'local_easyadmin'), ['class' => 'btn btn-secondary']);
    echo html_writer::end_tag('form');

} else if ($movestep >= 2 && $movesourcecategoryid > 0) {
    // --- Schritt 2: Kurse auswählen + Zielkategorie ---
    $mcourses = $DB->get_records('course', ['category' => $movesourcecategoryid], 'fullname ASC', 'id, fullname');

    if (empty($mcourses)) {
        echo html_writer::div(get_string('archive_nocoursesincategory', 'local_easyadmin'), 'alert alert-info');
        echo html_writer::link($returnurl, get_string('archive_cancel_button', 'local_easyadmin'), ['class' => 'btn btn-secondary']);
    } else {
        $categoryoptions = \core_course_category::make_categories_list('moodle/category:manage');
        // Die Quellkategorie selbst als Ziel auszuschließen wäre zwar
        // sauberer, ist aber keine sicherheitsrelevante Notwendigkeit -
        // Moodles move_courses() handhabt ein "Verschieben" in dieselbe
        // Kategorie ohnehin folgenlos (keine Änderung).

        echo html_writer::start_tag('form', ['method' => 'post', 'action' => $returnurl->out(false)]);
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'movestep', 'value' => 3]);

        echo html_writer::start_div('form-check mb-2 font-weight-bold');
        echo html_writer::empty_tag('input', [
            'type' => 'checkbox', 'id' => 'selectall_move', 'checked' => 'checked', 'class' => 'form-check-input',
        ]);
        echo html_writer::tag('label', get_string('selectall_easyadmin', 'local_easyadmin'), [
            'for' => 'selectall_move', 'class' => 'form-check-label',
        ]);
        echo html_writer::end_div();

        foreach ($mcourses as $mcourse) {
            $checkboxid = 'movecourse_' . $mcourse->id;
            echo html_writer::start_div('form-check');
            echo html_writer::empty_tag('input', [
                'type' => 'checkbox', 'name' => 'movecourseids[]', 'value' => $mcourse->id,
                'id' => $checkboxid, 'checked' => 'checked', 'class' => 'form-check-input',
            ]);
            echo html_writer::tag('label', format_string($mcourse->fullname), [
                'for' => $checkboxid, 'class' => 'form-check-label',
            ]);
            echo html_writer::end_div();
        }

        echo html_writer::start_div('mt-3');
        echo html_writer::label(get_string('move_targetcategory_easyadmin', 'local_easyadmin'), 'movetargetcategoryid');
        echo html_writer::select($categoryoptions, 'movetargetcategoryid', '', false, ['class' => 'form-control d-inline-block w-auto']);
        echo html_writer::end_div();

        echo html_writer::tag('button', get_string('archive_continue_button', 'local_easyadmin'), [
            'type' => 'submit', 'class' => 'btn btn-primary mt-3 mr-2',
        ]);
        echo html_writer::link($returnurl, get_string('archive_cancel_button', 'local_easyadmin'), ['class' => 'btn btn-secondary mt-3']);
        echo html_writer::end_tag('form');

        echo html_writer::script("
            document.getElementById('selectall_move').addEventListener('change', function() {
                var checked = this.checked;
                document.querySelectorAll('input[name=\"movecourseids[]\"]').forEach(function(cb) {
                    cb.checked = checked;
                });
            });
        ");
    }

} else {
    // --- Schritt 1: Quell-Kategorie wählen ---
    $categoryoptions = \core_course_category::make_categories_list('moodle/category:manage');

    if (empty($categoryoptions)) {
        echo html_writer::div(get_string('archive_nocategories', 'local_easyadmin'), 'alert alert-info');
    } else {
        echo html_writer::start_tag('form', ['method' => 'get', 'action' => $returnurl->out(false)]);
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'movestep', 'value' => 2]);
        echo html_writer::select($categoryoptions, 'movesourcecategoryid', '', false, ['class' => 'form-control d-inline-block w-auto']);
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
