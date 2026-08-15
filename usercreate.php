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
 * Nutzer & Gruppen-Unterseite: Nutzer anlegen (einzeln oder mehrere aus
 * einer Liste). Massen-Erstellung vergibt bewusst KEINE Admin-Rechte -
 * dafür ausschließlich das Einzelformular, siehe dortige Absicherung.
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/user/lib.php');

require_login();

$context = context_system::instance();
require_capability('local/easyadmin:manageusers', $context);

$returnurl = new moodle_url('/local/easyadmin/usercreate.php');
$dashboardurl = new moodle_url('/local/easyadmin/index.php');
$usersurl = new moodle_url('/local/easyadmin/users.php');

$PAGE->set_url($returnurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('createusersheader', 'local_easyadmin'));
$PAGE->set_heading(get_string('createusersheader', 'local_easyadmin'));
$PAGE->navbar->add(get_string('dashboardtitle', 'local_easyadmin'), $dashboardurl);
$PAGE->navbar->add(get_string('tile_users_title', 'local_easyadmin'), $usersurl);
$PAGE->navbar->add(get_string('createusersheader', 'local_easyadmin'));

// Das Häkchen "Zum Administrator machen" darf ausschließlich Personen
// angeboten werden, die selbst bereits ECHTE Website-Administrator/innen
// sind (also tatsächlich in $CFG->siteadmins stehen) - bewusst is_siteadmin()
// statt einer Capability-Prüfung wie 'moodle/site:config', da Letztere auch
// über eine speziell zugewiesene Rolle erfüllt sein könnte, ohne dass die
// Person tatsächlich vollen Admin-Status hat.
$cancreateadmin = is_siteadmin();

$userform = new \local_easyadmin\form\user_create_form(
    $returnurl,
    ['showmakeadmin' => $cancreateadmin]
);

if ($userform->is_cancelled()) {
    redirect($usersurl);
} else if ($userdata = $userform->get_data()) {
    // Die Methode moodleform::get_data() prüft den Sesskey bereits intern
    // (liefert bei ungültigem Sesskey null zurück, siehe is_cancelled()-Zweig
    // oben). Zusätzlicher expliziter Aufruf hier als Verteidigung in der
    // Tiefe und damit statische Analysewerkzeuge (Semgrep) diesen Schutz
    // auch anhand des Dateitexts erkennen, nicht nur anhand des
    // moodleform-Verhaltens.
    require_sesskey();

    $newuser = new stdClass();
    $newuser->auth = 'manual';
    $newuser->confirmed = 1;
    $newuser->mnethostid = $CFG->mnet_localhost_id;
    $newuser->username = \core_text::strtolower(trim($userdata->username));
    $newuser->firstname = $userdata->firstname;
    $newuser->lastname = $userdata->lastname;
    $newuser->email = $userdata->email;
    $newuser->password = $userdata->password;

    $newuserid = user_create_user($newuser, true, true);

    if (!empty($userdata->forcepasswordchange)) {
        set_user_preference('auth_forcepasswordchange', 1, $newuserid);
    }

    // Admin-Rechte vergeben: nur möglich, wenn oben bereits geprüft wurde,
    // dass die aktuelle Person selbst vollen Admin-Zugriff hat. Zusätzliche
    // serverseitige Prüfung hier, falls das Formular manipuliert würde.
    if (!empty($userdata->makeadmin) && $cancreateadmin) {
        $admins = array_filter(array_map('trim', explode(',', $CFG->siteadmins)));
        $admins[] = (string) $newuserid;
        $admins = array_unique($admins);

        // Manueller Eintrag ins Config-Log, da set_config() diesen
        // sicherheitskritischen Vorgang nicht automatisch protokolliert.
        $configlog = new stdClass();
        $configlog->userid = $USER->id;
        $configlog->timemodified = time();
        $configlog->plugin = null;
        $configlog->name = 'siteadmins';
        $configlog->value = implode(',', $admins);
        $configlog->oldvalue = $CFG->siteadmins;
        $DB->insert_record('config_log', $configlog);

        set_config('siteadmins', implode(',', $admins));
    }

    redirect(
        $returnurl,
        get_string('usercreated_easyadmin', 'local_easyadmin', fullname($newuser)),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

// -----------------------------------------------------------------------
// Nutzer anlegen: mehrere auf einmal aus einer Liste
// (Format je Zeile: Vorname;Nachname;E-Mail;Anmeldename).
// Passwörter werden automatisch generiert und nach dem Anlegen angezeigt,
// da sie sonst nirgends sichtbar wären.
$bulkuserform = new \local_easyadmin\form\bulk_user_create_form($returnurl);
$createdusers = [];

if ($bulkuserdata = $bulkuserform->get_data()) {
    $lines = array_filter(array_map('trim', explode("\n", str_replace("\r", '', $bulkuserdata->entries))));

    $createdcount = 0;
    $skippedcount = 0;
    $invalidcount = 0;

    foreach ($lines as $line) {
        $parts = array_map('trim', explode(';', $line));

        if (count($parts) !== 4 || in_array('', $parts, true)) {
            $invalidcount++;
            continue;
        }

        [$firstname, $lastname, $email, $username] = $parts;
        $username = \core_text::strtolower($username);

        if (
            !validate_email($email)
            || $DB->record_exists('user', ['username' => $username, 'mnethostid' => $CFG->mnet_localhost_id])
            || (
                empty($CFG->allowaccountssameemail)
                && $DB->record_exists('user', [
                    'email' => $email,
                    'mnethostid' => $CFG->mnet_localhost_id,
                    'deleted' => 0,
                ])
            )
        ) {
            $skippedcount++;
            continue;
        }

        $password = generate_password(12);

        $newuser = new stdClass();
        $newuser->auth = 'manual';
        $newuser->confirmed = 1;
        $newuser->mnethostid = $CFG->mnet_localhost_id;
        $newuser->username = $username;
        $newuser->firstname = $firstname;
        $newuser->lastname = $lastname;
        $newuser->email = $email;
        $newuser->password = $password;

        $newuserid = user_create_user($newuser, true, true);

        if (!empty($bulkuserdata->forcepasswordchange)) {
            set_user_preference('auth_forcepasswordchange', 1, $newuserid);
        }

        $createdusers[] = [
            'fullname' => fullname($newuser),
            'username' => $username,
            'password' => $password,
        ];
        $createdcount++;
    }

    // Bewusst KEIN redirect() hier (anders als bei den übrigen Formularen),
    // da die generierten Passwörter dem Admin direkt im Anschluss angezeigt
    // werden müssen - nach einem redirect wären sie unwiederbringlich weg.
    $bulkusersummary = get_string('bulkuser_created_easyadmin', 'local_easyadmin', (object) [
        'created' => $createdcount,
        'skipped' => $skippedcount,
        'invalid' => $invalidcount,
    ]);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('createusersheader', 'local_easyadmin'));
echo html_writer::tag('p', get_string('createusersintro', 'local_easyadmin'), ['class' => 'text-muted']);
$userform->display();

echo html_writer::tag('h4', get_string('bulkuserheader', 'local_easyadmin'), ['class' => 'mt-3']);
echo html_writer::tag('p', get_string('bulkuserintro', 'local_easyadmin'), ['class' => 'text-muted']);

if (!empty($createdusers)) {
    echo html_writer::div($bulkusersummary, 'alert alert-success');
    echo html_writer::div(get_string('bulkuser_passwordnotice', 'local_easyadmin'), 'alert alert-warning');

    $table = new html_table();
    $table->head = [
        get_string('fullname'),
        get_string('username'),
        get_string('password'),
    ];
    foreach ($createdusers as $entry) {
        $table->data[] = [$entry['fullname'], $entry['username'], $entry['password']];
    }
    echo html_writer::table($table);
} else {
    $bulkuserform->display();
}

echo html_writer::div(
    html_writer::link($usersurl, get_string('backtousers_easyadmin', 'local_easyadmin')),
    'mt-3'
);

echo $OUTPUT->footer();
