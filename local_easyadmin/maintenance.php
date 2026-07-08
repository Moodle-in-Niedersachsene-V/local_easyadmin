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
 * Wartung & Berichte: Debug-Modus, Caches leeren, einfache Zugriffszahlen.
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');

require_login();

$context = context_system::instance();
require_capability('local/easyadmin:managemaintenance', $context);

$returnurl = new moodle_url('/local/easyadmin/maintenance.php');
$dashboardurl = new moodle_url('/local/easyadmin/index.php');

$PAGE->set_url($returnurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('tile_maintenance_title', 'local_easyadmin'));
$PAGE->set_heading(get_string('tile_maintenance_title', 'local_easyadmin'));
$PAGE->navbar->add(
    get_string('dashboardtitle', 'local_easyadmin'),
    $dashboardurl
);
$PAGE->navbar->add(get_string('tile_maintenance_title', 'local_easyadmin'));

// Caches leeren: eigene, kleine Aktion mit Sesskey-Schutz (kein Formular
// nötig, da keine Eingabefelder verarbeitet werden).
if (optional_param('purgecaches', 0, PARAM_BOOL)) {
    require_sesskey();
    purge_all_caches();
    redirect(
        $returnurl,
        get_string('cachespurged', 'local_easyadmin'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

$currentloglifetimeraw = get_config('logstore_standard', 'loglifetime');
$currentloglifetime = ($currentloglifetimeraw === false) ? 0 : (int) $currentloglifetimeraw;

$mform = new \local_easyadmin\form\maintenance_form();
$logretentionform = new \local_easyadmin\form\logretention_form(
    $returnurl,
    ['currentloglifetime' => $currentloglifetime]
);

if ($mform->is_cancelled()) {
    redirect($dashboardurl);
} else if ($data = $mform->get_data()) {
    if (!empty($data->debugmode_enabled)) {
        set_config('debug', DEBUG_DEVELOPER);
        set_config('debugdisplay', 1);
    } else {
        set_config('debug', DEBUG_NONE);
        set_config('debugdisplay', 0);
    }

    redirect(
        $returnurl,
        get_string('maintenancesaved', 'local_easyadmin'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
} else if ($logretentiondata = $logretentionform->get_data()) {
    // Log-Aufbewahrung ist eine Einstellung des Standard-Logspeichers
    // (eigener Plugin-Namespace 'logstore_standard', kein Core-$CFG-Wert).
    set_config('loglifetime', (int) $logretentiondata->loglifetime, 'logstore_standard');

    redirect(
        $returnurl,
        get_string('maintenancesaved', 'local_easyadmin'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
} else {
    $currentdata = new stdClass();
    $currentdata->debugmode_enabled = ((int) get_config('core', 'debug')) > 0;
    $mform->set_data($currentdata);

    $logretentioncurrentdata = new stdClass();
    $logretentioncurrentdata->loglifetime = $currentloglifetime;
    $logretentionform->set_data($logretentioncurrentdata);
}

// Einfache Zugriffszahlen aus dem Standard-Logspeicher. Bewusst defensiv
// (try/catch), da nicht jede Moodle-Instanz zwingend den Standard-Logstore
// aktiv hat.
$accessescount = null;
$activeuserscount = null;
try {
    $since = time() - (7 * DAYSECS);
    $accessescount = $DB->count_records_select('logstore_standard_log', 'timecreated > :since', ['since' => $since]);
    $activeuserscount = $DB->count_records_sql(
        'SELECT COUNT(DISTINCT userid) FROM {logstore_standard_log} WHERE timecreated > :since',
        ['since' => $since]
    );
} catch (\Throwable $e) {
    debugging('local_easyadmin: Zugriffszahlen konnten nicht ermittelt werden: ' . $e->getMessage());
}

// Nutzer- und Kurszahlen: einfache, stabile Kernabfragen (Gastkonto und
// gelöschte Nutzer/innen bzw. der Startseiten-Pseudokurs werden ausgenommen).
$usercount = $DB->count_records_select(
    'user',
    'deleted = 0 AND id <> :guestid',
    ['guestid' => $CFG->siteguest]
);
$coursecount = $DB->count_records_select('course', 'id <> :siteid', ['siteid' => SITEID]);

$logreporturl = new moodle_url('/report/log/index.php', ['id' => SITEID]);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('tile_maintenance_title', 'local_easyadmin'));

// Debug-Modus-Hinweis, falls aktuell aktiv - gut sichtbar, damit es nicht
// vergessen wird.
if (((int) get_config('core', 'debug')) > 0) {
    echo html_writer::div(get_string('debugmode_active_notice', 'local_easyadmin'), 'alert alert-danger');
}

echo $OUTPUT->heading(get_string('maintenanceheader', 'local_easyadmin'), 3);
$mform->display();

echo html_writer::tag('h3', get_string('cachesheader', 'local_easyadmin'));
echo html_writer::tag('p', get_string('cachesintro', 'local_easyadmin'), ['class' => 'text-muted']);
$purgeurl = new moodle_url('/local/easyadmin/maintenance.php', ['purgecaches' => 1, 'sesskey' => sesskey()]);
echo html_writer::link($purgeurl, get_string('purgecachesbutton', 'local_easyadmin'), ['class' => 'btn btn-secondary']);

echo html_writer::tag('h3', get_string('reportsheader', 'local_easyadmin'), ['class' => 'mt-4']);
echo html_writer::tag('p', get_string('reportsintro', 'local_easyadmin'), ['class' => 'text-muted']);

echo html_writer::tag('h4', get_string('logretentionheader', 'local_easyadmin'));
$logretentionform->display();

echo html_writer::start_div('row');
echo html_writer::start_div('col-md-6');
echo html_writer::div(get_string('usercount_easyadmin', 'local_easyadmin', $usercount), 'card p-3 mb-2');
echo html_writer::end_div();
echo html_writer::start_div('col-md-6');
echo html_writer::div(get_string('coursecount_easyadmin', 'local_easyadmin', $coursecount), 'card p-3 mb-2');
echo html_writer::end_div();
echo html_writer::end_div();

if ($accessescount !== null) {
    echo html_writer::start_div('row');
    echo html_writer::start_div('col-md-6');
    echo html_writer::div(get_string('accessescount_easyadmin', 'local_easyadmin', $accessescount), 'card p-3 mb-2');
    echo html_writer::end_div();
    echo html_writer::start_div('col-md-6');
    echo html_writer::div(get_string('activeuserscount_easyadmin', 'local_easyadmin', $activeuserscount), 'card p-3 mb-2');
    echo html_writer::end_div();
    echo html_writer::end_div();
} else {
    echo html_writer::div(get_string('accessescount_unavailable', 'local_easyadmin'), 'alert alert-info');
}

echo html_writer::link($logreporturl, get_string('viewfulllog_easyadmin', 'local_easyadmin'), ['class' => 'btn btn-secondary']);
echo html_writer::tag('p', get_string('viewfulllog_easyadmin_notice', 'local_easyadmin'), ['class' => 'text-muted mt-2']);

echo $OUTPUT->footer();
