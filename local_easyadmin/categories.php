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
 * Kurse-Unterseite: Kategorien erstellen (einzeln oder mehrere aus einer
 * Liste).
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');

require_login();

$context = context_system::instance();
require_capability('local/easyadmin:managecourses', $context);

$returnurl = new moodle_url('/local/easyadmin/categories.php');
$dashboardurl = new moodle_url('/local/easyadmin/index.php');
$coursesurl = new moodle_url('/local/easyadmin/courses.php');

$PAGE->set_url($returnurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('categoriesheader', 'local_easyadmin'));
$PAGE->set_heading(get_string('categoriesheader', 'local_easyadmin'));
$PAGE->navbar->add(get_string('dashboardtitle', 'local_easyadmin'), $dashboardurl);
$PAGE->navbar->add(get_string('tile_courses_title', 'local_easyadmin'), $coursesurl);
$PAGE->navbar->add(get_string('categoriesheader', 'local_easyadmin'));

// -----------------------------------------------------------------------
// Kurskategorien: Formular zum Anlegen.
// -----------------------------------------------------------------------
$categoryform = new \local_easyadmin\form\category_create_form($returnurl);

if ($categorydata = $categoryform->get_data()) {
    $newcategory = new stdClass();
    $newcategory->name = $categorydata->name;
    $newcategory->parent = (int) $categorydata->parent;
    $newcategory->description = $categorydata->description;
    $newcategory->descriptionformat = FORMAT_PLAIN;

    \core_course_category::create($newcategory);

    redirect(
        $returnurl,
        get_string('categorycreated_easyadmin', 'local_easyadmin', $categorydata->name),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

// -----------------------------------------------------------------------
// Kurskategorien: mehrere Unterkategorien auf einmal aus einer Liste
// anlegen (z. B. eine Kategorie je Lehrkraft-Kürzel).
// -----------------------------------------------------------------------
$bulkcategoryform = new \local_easyadmin\form\bulk_category_create_form($returnurl);

if ($bulkdata = $bulkcategoryform->get_data()) {
    $lines = array_filter(array_map('trim', explode("\n", str_replace("\r", '', $bulkdata->names))));
    $parentid = (int) $bulkdata->parent;

    $createdcount = 0;
    $skippedcount = 0;

    foreach ($lines as $name) {
        // Bereits vorhandene Unterkategorie mit demselben Namen unter
        // demselben Elternteil überspringen statt zu duplizieren - so kann
        // dieselbe Liste z. B. im nächsten Schuljahr gefahrlos erneut
        // eingefügt werden, ohne Dubletten zu erzeugen.
        $exists = $DB->record_exists('course_categories', ['name' => $name, 'parent' => $parentid]);
        if ($exists) {
            $skippedcount++;
            continue;
        }

        $newcategory = new stdClass();
        $newcategory->name = $name;
        $newcategory->parent = $parentid;

        \core_course_category::create($newcategory);
        $createdcount++;
    }

    redirect(
        $returnurl,
        get_string('bulkcategory_created_easyadmin', 'local_easyadmin', (object) [
            'created' => $createdcount,
            'skipped' => $skippedcount,
        ]),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('categoriesheader', 'local_easyadmin'));
echo html_writer::tag('p', get_string('categoriesintro', 'local_easyadmin'), ['class' => 'text-muted']);
$categoryform->display();

$categorycount = $DB->count_records('course_categories');
echo html_writer::tag('p', get_string('categorycount_easyadmin', 'local_easyadmin', $categorycount));

echo html_writer::tag('h4', get_string('bulkcategoryheader', 'local_easyadmin'), ['class' => 'mt-3']);
echo html_writer::tag('p', get_string('bulkcategoryintro', 'local_easyadmin'), ['class' => 'text-muted']);
$bulkcategoryform->display();

echo html_writer::div(
    html_writer::link($coursesurl, get_string('backtocourses_easyadmin', 'local_easyadmin')),
    'mt-3'
);

echo $OUTPUT->footer();
