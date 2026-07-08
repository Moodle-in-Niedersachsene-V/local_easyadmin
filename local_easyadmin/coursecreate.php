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
 * Kurse-Unterseite: Kurse anlegen (einzeln oder mehrere aus einer Liste).
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once(__DIR__ . '/lib.php');

require_login();

$context = context_system::instance();
require_capability('local/easyadmin:managecourses', $context);

$returnurl = new moodle_url('/local/easyadmin/coursecreate.php');
$dashboardurl = new moodle_url('/local/easyadmin/index.php');
$coursesurl = new moodle_url('/local/easyadmin/courses.php');

$PAGE->set_url($returnurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('createcoursesheader', 'local_easyadmin'));
$PAGE->set_heading(get_string('createcoursesheader', 'local_easyadmin'));
$PAGE->navbar->add(get_string('dashboardtitle', 'local_easyadmin'), $dashboardurl);
$PAGE->navbar->add(get_string('tile_courses_title', 'local_easyadmin'), $coursesurl);
$PAGE->navbar->add(get_string('createcoursesheader', 'local_easyadmin'));

// -----------------------------------------------------------------------
// Kurse anlegen: einzeln.
// -----------------------------------------------------------------------
$courseform = new \local_easyadmin\form\course_create_form($returnurl);

if ($coursedata = $courseform->get_data()) {
    $newcourse = new stdClass();
    $newcourse->fullname = $coursedata->fullname;
    $newcourse->shortname = $coursedata->shortname;
    $newcourse->category = (int) $coursedata->category;
    $newcourse->visible = !empty($coursedata->visible) ? 1 : 0;

    $createdcourse = create_course($newcourse);

    local_easyadmin_enrol_teacher_and_cohort(
        $createdcourse->id,
        $coursedata->teacheridentifier,
        (int) $coursedata->cohortid
    );

    redirect(
        $returnurl,
        get_string('coursecreated_easyadmin', 'local_easyadmin', $coursedata->fullname),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

// -----------------------------------------------------------------------
// Kurse anlegen: mehrere auf einmal aus einer Liste
// (Format je Zeile: Vollständiger Name;Kurzbezeichnung).
// -----------------------------------------------------------------------
$bulkcourseform = new \local_easyadmin\form\bulk_course_create_form($returnurl);

if ($bulkcoursedata = $bulkcourseform->get_data()) {
    $lines = array_filter(array_map('trim', explode("\n", str_replace("\r", '', $bulkcoursedata->entries))));
    $categoryid = (int) $bulkcoursedata->category;

    $createdcount = 0;
    $skippedcount = 0;
    $invalidcount = 0;

    foreach ($lines as $line) {
        $parts = array_map('trim', explode(';', $line));

        if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            $invalidcount++;
            continue;
        }

        [$fullname, $shortname] = $parts;

        if ($DB->record_exists('course', ['shortname' => $shortname])) {
            $skippedcount++;
            continue;
        }

        $newcourse = new stdClass();
        $newcourse->fullname = $fullname;
        $newcourse->shortname = $shortname;
        $newcourse->category = $categoryid;
        $newcourse->visible = 1;

        $createdcourse = create_course($newcourse);

        local_easyadmin_enrol_teacher_and_cohort(
            $createdcourse->id,
            $bulkcoursedata->teacheridentifier,
            (int) $bulkcoursedata->cohortid
        );

        $createdcount++;
    }

    redirect(
        $returnurl,
        get_string('bulkcourse_created_easyadmin', 'local_easyadmin', (object) [
            'created' => $createdcount,
            'skipped' => $skippedcount,
            'invalid' => $invalidcount,
        ]),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('createcoursesheader', 'local_easyadmin'));
echo html_writer::tag('p', get_string('createcoursesintro', 'local_easyadmin'), ['class' => 'text-muted']);
$courseform->display();

echo html_writer::tag('h4', get_string('bulkcourseheader', 'local_easyadmin'), ['class' => 'mt-3']);
echo html_writer::tag('p', get_string('bulkcourseintro', 'local_easyadmin'), ['class' => 'text-muted']);
$bulkcourseform->display();

echo html_writer::div(
    html_writer::link($coursesurl, get_string('backtocourses_easyadmin', 'local_easyadmin')),
    'mt-3'
);

echo $OUTPUT->footer();
