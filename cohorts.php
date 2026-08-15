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
 * Nutzer & Gruppen-Unterseite: Globale Gruppen (Kohorten) anlegen, einzeln
 * oder mehrere aus einer Liste.
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/cohort/lib.php');

require_login();

$context = context_system::instance();
require_capability('local/easyadmin:manageusers', $context);

$returnurl = new moodle_url('/local/easyadmin/cohorts.php');
$dashboardurl = new moodle_url('/local/easyadmin/index.php');
$usersurl = new moodle_url('/local/easyadmin/users.php');

$PAGE->set_url($returnurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('cohortsheader', 'local_easyadmin'));
$PAGE->set_heading(get_string('cohortsheader', 'local_easyadmin'));
$PAGE->navbar->add(get_string('dashboardtitle', 'local_easyadmin'), $dashboardurl);
$PAGE->navbar->add(get_string('tile_users_title', 'local_easyadmin'), $usersurl);
$PAGE->navbar->add(get_string('cohortsheader', 'local_easyadmin'));

$cohortform = new \local_easyadmin\form\cohort_create_form($returnurl);

if ($cohortform->is_cancelled()) {
    redirect($usersurl);
} else if ($cohortdata = $cohortform->get_data()) {
    $cohort = new stdClass();
    $cohort->contextid = $context->id;
    $cohort->name = $cohortdata->name;
    $cohort->idnumber = trim((string) $cohortdata->idnumber);
    $cohort->description = $cohortdata->description;
    $cohort->descriptionformat = FORMAT_PLAIN;
    $cohort->visible = 1;

    cohort_add_cohort($cohort);

    redirect(
        $returnurl,
        get_string('cohortcreated_easyadmin', 'local_easyadmin', $cohortdata->name),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

// -----------------------------------------------------------------------
// Mehrere globale Gruppen auf einmal aus einer Liste anlegen.
$bulkcohortform = new \local_easyadmin\form\bulk_cohort_create_form($returnurl);

if ($bulkcohortdata = $bulkcohortform->get_data()) {
    $lines = array_filter(array_map('trim', explode("\n", str_replace("\r", '', $bulkcohortdata->names))));

    $createdcount = 0;
    $skippedcount = 0;

    foreach ($lines as $name) {
        $exists = $DB->record_exists('cohort', ['name' => $name, 'contextid' => $context->id]);
        if ($exists) {
            $skippedcount++;
            continue;
        }

        $cohort = new stdClass();
        $cohort->contextid = $context->id;
        $cohort->name = $name;
        $cohort->visible = 1;

        cohort_add_cohort($cohort);
        $createdcount++;
    }

    redirect(
        $returnurl,
        get_string('bulkcohort_created_easyadmin', 'local_easyadmin', (object) [
            'created' => $createdcount,
            'skipped' => $skippedcount,
        ]),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

$cohortcount = $DB->count_records('cohort');
$cohortmanageurl = new moodle_url('/cohort/index.php');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('cohortsheader', 'local_easyadmin'));
echo html_writer::tag('p', get_string('cohortsintro', 'local_easyadmin'), ['class' => 'text-muted']);
echo html_writer::tag('p', get_string('cohortcount_easyadmin', 'local_easyadmin', $cohortcount));
$cohortform->display();

echo html_writer::tag('h4', get_string('bulkcohortheader', 'local_easyadmin'), ['class' => 'mt-3']);
echo html_writer::tag('p', get_string('bulkcohortintro', 'local_easyadmin'), ['class' => 'text-muted']);
$bulkcohortform->display();

echo html_writer::link(
    $cohortmanageurl,
    get_string('managecohorts_easyadmin', 'local_easyadmin'),
    ['class' => 'btn btn-secondary mt-2']
);
echo html_writer::tag('p', get_string('managecohorts_easyadmin_notice', 'local_easyadmin'), ['class' => 'text-muted mt-2']);

echo html_writer::div(
    html_writer::link($usersurl, get_string('backtousers_easyadmin', 'local_easyadmin')),
    'mt-3'
);

echo $OUTPUT->footer();
