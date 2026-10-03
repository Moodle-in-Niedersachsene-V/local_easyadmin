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
 * Nutzer & Gruppen-Unterseite: Nutzerkonten löschen.
 *
 * Drei Funktionen gebündelt: (1) einzelne/gefilterte Konten manuell löschen,
 * mit optionaler Eingrenzung auf eine globale Gruppe, (2) automatische
 * Löschung inaktiver Konten nach einstellbarer Frist (Einstellung +
 * Live-Vorschau; die eigentliche Ausführung läuft über eine geplante
 * Aufgabe, siehe classes/task/cleanup_inactive_users.php).
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once(__DIR__ . '/lib.php');

require_login();

$context = context_system::instance();
require_capability('local/easyadmin:manageusers', $context);
// Zusätzlich zu unserer eigenen Dashboard-Capability wird echte,
// Moodle-weite Nutzerlösch-Berechtigung verlangt - dieselbe, die auch
// Website-Administration > Nutzer/innen > Nutzer/innen löschen voraussetzt.
require_capability('moodle/user:delete', $context);

$returnurl = new moodle_url('/local/easyadmin/userdelete.php');
$dashboardurl = new moodle_url('/local/easyadmin/index.php');
$usersurl = new moodle_url('/local/easyadmin/users.php');

$PAGE->set_url($returnurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('userdeleteheader', 'local_easyadmin'));
$PAGE->set_heading(get_string('userdeleteheader', 'local_easyadmin'));
$PAGE->navbar->add(get_string('dashboardtitle', 'local_easyadmin'), $dashboardurl);
$PAGE->navbar->add(get_string('tile_users_title', 'local_easyadmin'), $usersurl);
$PAGE->navbar->add(get_string('userdeleteheader', 'local_easyadmin'));

// -----------------------------------------------------------------------
// Nutzerkonten löschen: dreistufiger Ablauf wie "Kurse löschen", mit
// zusätzlicher Pflicht-Bestätigung, da Kontolöschung nicht rückgängig zu
// machen ist. Website-Administrator/innen, das Gastkonto und das eigene
// Konto sind durchgehend ausgeschlossen, nicht erst bei der Ausführung.
$userdeletestep = optional_param('userdeletestep', 0, PARAM_INT);
$filtercohortid = optional_param('filtercohortid', 0, PARAM_INT);
$deleteuserids = optional_param_array('deleteuserids', [], PARAM_INT);
$deletedcount = 0;

if ($userdeletestep === 3 && optional_param('confirmuserdelete', 0, PARAM_BOOL)) {
    require_sesskey();

    if (empty(optional_param('userdeleteacknowledge', 0, PARAM_BOOL))) {
        redirect(
            $returnurl,
            get_string('error_userdeletenotacknowledged', 'local_easyadmin'),
            null,
            \core\output\notification::NOTIFY_ERROR
        );
    }

    if (empty($deleteuserids)) {
        redirect(
            $returnurl,
            get_string('error_nousersselected', 'local_easyadmin'),
            null,
            \core\output\notification::NOTIFY_ERROR
        );
    }

    foreach ($deleteuserids as $userid) {
        $userrecord = $DB->get_record('user', ['id' => $userid, 'deleted' => 0]);
        if (
            !$userrecord || is_siteadmin($userrecord) || $userrecord->id == $USER->id
                || $userrecord->id == $CFG->siteguest
        ) {
            // Verteidigung in der Tiefe: dieselbe Ausnahmeliste wie bei der
            // Anzeige in Schritt 2, hier noch einmal unmittelbar vor der
            // eigentlichen Löschung geprüft.
            continue;
        }

        user_delete_user($userrecord);
        $deletedcount++;
    }

    redirect(
        $returnurl,
        get_string('userdeleted_success_easyadmin', 'local_easyadmin', $deletedcount),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

// -----------------------------------------------------------------------
// Automatische Löschung inaktiver Konten: Einstellungsformular.
$inactiveform = new \local_easyadmin\form\inactivedeletion_form($returnurl);

if ($inactivedata = $inactiveform->get_data()) {
    set_config('inactivedeletion_enabled', !empty($inactivedata->inactivedeletion_enabled) ? 1 : 0, 'local_easyadmin');
    set_config('inactivedeletion_days', (int) $inactivedata->inactivedeletion_days, 'local_easyadmin');

    redirect(
        $returnurl,
        get_string('inactivedeletion_saved', 'local_easyadmin'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
} else {
    $currentinactivedata = new stdClass();
    $currentinactivedata->inactivedeletion_enabled = (bool) get_config('local_easyadmin', 'inactivedeletion_enabled');
    $storeddays = (int) get_config('local_easyadmin', 'inactivedeletion_days');
    $currentinactivedata->inactivedeletion_days = $storeddays > 0 ? $storeddays : LOCAL_EASYADMIN_INACTIVEDELETION_MINDAYS;
    $inactiveform->set_data($currentinactivedata);
}

$previewdays = (int) get_config('local_easyadmin', 'inactivedeletion_days');
if ($previewdays < LOCAL_EASYADMIN_INACTIVEDELETION_MINDAYS) {
    $previewdays = LOCAL_EASYADMIN_INACTIVEDELETION_MINDAYS;
}
$previewcount = count(local_easyadmin_get_inactive_users($previewdays));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('userdeleteheader', 'local_easyadmin'));
echo html_writer::div(get_string('userdeleteintro', 'local_easyadmin'), 'alert alert-danger');

// Schritt 3: Bestätigung mit Pflicht-Häkchen.
if ($userdeletestep === 3 && !empty($deleteuserids)) {
    [$insql, $inparams] = $DB->get_in_or_equal($deleteuserids);
    $dusers = $DB->get_records_select(
        'user',
        "id $insql",
        $inparams,
        'lastname ASC, firstname ASC',
        'id, firstname, lastname, username'
    );

    echo html_writer::div(get_string('userdelete_confirm_intro', 'local_easyadmin'), 'alert alert-warning');
    echo html_writer::start_tag('ul');
    foreach ($dusers as $duser) {
        echo html_writer::tag('li', fullname($duser) . ' (' . s($duser->username) . ')');
    }
    echo html_writer::end_tag('ul');

    echo html_writer::start_tag('form', ['method' => 'post', 'action' => $returnurl->out(false)]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'userdeletestep', 'value' => 3]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'confirmuserdelete', 'value' => 1]);
    foreach ($deleteuserids as $userid) {
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'deleteuserids[]', 'value' => $userid]);
    }

    echo html_writer::start_div('form-check mb-3');
    echo html_writer::empty_tag('input', [
        'type' => 'checkbox', 'name' => 'userdeleteacknowledge', 'value' => 1,
        'id' => 'userdeleteacknowledge', 'class' => 'form-check-input', 'required' => 'required',
    ]);
    echo html_writer::tag('label', get_string('userdelete_acknowledge_easyadmin', 'local_easyadmin'), [
        'for' => 'userdeleteacknowledge', 'class' => 'form-check-label font-weight-bold',
    ]);
    echo html_writer::end_div();

    echo html_writer::tag('button', get_string('userdelete_confirm_button', 'local_easyadmin'), [
        'type' => 'submit', 'class' => 'btn btn-danger mr-2',
    ]);
    echo html_writer::link($returnurl, get_string('archive_cancel_button', 'local_easyadmin'), ['class' => 'btn btn-secondary']);
    echo html_writer::end_tag('form');

    // Schritt 2: passende Nutzer/innen auswählen.
} else if ($userdeletestep >= 2) {
    if ($filtercohortid > 0) {
        $dusers = $DB->get_records_sql(
            'SELECT u.id, u.firstname, u.lastname, u.username, u.lastaccess
               FROM {user} u
               JOIN {cohort_members} cm ON cm.userid = u.id
              WHERE cm.cohortid = :cohortid AND u.deleted = 0
           ORDER BY u.lastname ASC, u.firstname ASC',
            ['cohortid' => $filtercohortid]
        );
    } else {
        $dusers = $DB->get_records_select(
            'user',
            'deleted = 0',
            null,
            'lastname ASC, firstname ASC',
            'id, firstname, lastname, username, lastaccess'
        );
    }

    $dusers = array_filter($dusers, static function ($u) use ($USER, $CFG) {
        return !is_siteadmin($u) && $u->id != $USER->id && $u->id != $CFG->siteguest;
    });

    if (empty($dusers)) {
        echo html_writer::div(get_string('userdelete_nousersfound', 'local_easyadmin'), 'alert alert-info');
        echo html_writer::link(
            $returnurl,
            get_string('archive_cancel_button', 'local_easyadmin'),
            ['class' => 'btn btn-secondary']
        );
    } else {
        echo html_writer::start_tag('form', ['method' => 'post', 'action' => $returnurl->out(false)]);
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'userdeletestep', 'value' => 3]);

        echo html_writer::start_div('form-check mb-2 font-weight-bold');
        echo html_writer::empty_tag('input', [
            'type' => 'checkbox', 'id' => 'selectall_userdelete', 'class' => 'form-check-input',
        ]);
        echo html_writer::tag('label', get_string('selectall_easyadmin', 'local_easyadmin'), [
            'for' => 'selectall_userdelete', 'class' => 'form-check-label',
        ]);
        echo html_writer::end_div();

        foreach ($dusers as $duser) {
            $checkboxid = 'deleteuser_' . $duser->id;
            $lastaccesstext = $duser->lastaccess
                ? userdate($duser->lastaccess, get_string('strftimedatetimeshort', 'langconfig'))
                : get_string('userdelete_neveraccessed', 'local_easyadmin');

            echo html_writer::start_div('form-check');
            echo html_writer::empty_tag('input', [
                'type' => 'checkbox', 'name' => 'deleteuserids[]', 'value' => $duser->id,
                'id' => $checkboxid, 'class' => 'form-check-input',
            ]);
            echo html_writer::tag('label', fullname($duser) . ' (' . s($duser->username) . ') - ' . $lastaccesstext, [
                'for' => $checkboxid, 'class' => 'form-check-label',
            ]);
            echo html_writer::end_div();
        }

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
            document.getElementById('selectall_userdelete').addEventListener('change', function() {
                var checked = this.checked;
                document.querySelectorAll('input[name=\"deleteuserids[]\"]').forEach(function(cb) {
                    cb.checked = checked;
                });
            });
        ");
    }

    // Schritt 1: Filter wählen.
} else {
    $cohortoptions = [0 => get_string('userdelete_filter_all', 'local_easyadmin')];
    $cohorts = $DB->get_records('cohort', null, 'name ASC', 'id, name');
    foreach ($cohorts as $cohort) {
        $cohortoptions[$cohort->id] = format_string($cohort->name);
    }

    echo html_writer::start_tag('form', ['method' => 'get', 'action' => $returnurl->out(false)]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'userdeletestep', 'value' => 2]);
    echo html_writer::select($cohortoptions, 'filtercohortid', '', false, ['class' => 'form-control d-inline-block w-auto']);
    echo ' ';
    echo html_writer::tag('button', get_string('userdelete_choosefilter_button', 'local_easyadmin'), [
        'type' => 'submit', 'class' => 'btn btn-secondary',
    ]);
    echo html_writer::end_tag('form');
}

// Automatische Löschung: Einstellungen + Live-Vorschau.
echo html_writer::tag('h3', get_string('inactivedeletionheader', 'local_easyadmin'), ['class' => 'mt-4']);
echo html_writer::tag('p', get_string('inactivedeletionintro', 'local_easyadmin'), ['class' => 'text-muted']);
echo html_writer::tag('p', get_string('inactivedeletion_preview', 'local_easyadmin', $previewcount));
$inactiveform->display();

echo html_writer::div(
    html_writer::link($usersurl, get_string('backtousers_easyadmin', 'local_easyadmin')),
    'mt-3'
);

echo $OUTPUT->footer();
