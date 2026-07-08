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
 * Vereinfachte Sicherheits- und Datenschutz-Einstellungen.
 *
 * Bildet eine bewusst kleine, für Grundschul-Admins relevante Auswahl aus
 * Website-Administration > Allgemein > Sicherheit sowie
 * datenschutzbezogenen Kerneinstellungen ab.
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');

require_login();

$context = context_system::instance();
require_capability('local/easyadmin:manageprivacy', $context);

$PAGE->set_url(new moodle_url('/local/easyadmin/security.php'));
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('tile_privacy_title', 'local_easyadmin'));
$PAGE->set_heading(get_string('tile_privacy_title', 'local_easyadmin'));
$PAGE->navbar->add(
    get_string('dashboardtitle', 'local_easyadmin'),
    new moodle_url('/local/easyadmin/index.php')
);
$PAGE->navbar->add(get_string('tile_privacy_title', 'local_easyadmin'));

$returnurl = new moodle_url('/local/easyadmin/security.php');
$dashboardurl = new moodle_url('/local/easyadmin/index.php');

$mform = new \local_easyadmin\form\security_form();

if ($mform->is_cancelled()) {
    redirect($dashboardurl);
} else if ($data = $mform->get_data()) {

    // Alle Felder hier sind echte Moodle-Kerneinstellungen (kein
    // Plugin-Namespace), identisch zu Website-Administration > Allgemein >
    // Sicherheit bzw. den entsprechenden Datenschutz-Einstellungen.
    set_config('forcelogin', !empty($data->forcelogin) ? 1 : 0);
    set_config('guestloginbutton', !empty($data->guestloginbutton) ? 1 : 0);
    set_config('passwordpolicy', !empty($data->passwordpolicy) ? 1 : 0);

    if (!empty($data->passwordpolicy)) {
        set_config('minpasswordlength', (int) $data->minpasswordlength);
        set_config('minpassworddigits', (int) $data->minpassworddigits);
        set_config('minpasswordlower', (int) $data->minpasswordlower);
        set_config('minpasswordupper', (int) $data->minpasswordupper);
        set_config('minpasswordnonalphanum', (int) $data->minpasswordnonalphanum);
        set_config('maxconsecutiveidentchars', (int) $data->maxconsecutiveidentchars);
    }

    set_config('lockoutthreshold', (int) $data->lockoutthreshold);
    set_config('displayloginfailures', !empty($data->displayloginfailures) ? 1 : 0);
    set_config('allowframembedding', !empty($data->allowframembedding) ? 1 : 0);
    set_config('sitepolicy', $data->sitepolicy);

    // Ohne dies zeigt der Link zur Moodle-Richtlinienverwaltung einen
    // "Bereichsfehler", solange die Website noch auf dem einfachen
    // Standard-Link ('core') statt der vollen Richtlinienverwaltung
    // ('tool_policy') steht.
    set_config('sitepolicyhandler', !empty($data->sitepolicyhandler_usetool) ? 'tool_policy' : 'core');

    redirect(
        $returnurl,
        get_string('securitysaved', 'local_easyadmin'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
} else {
    $currentdata = new stdClass();
    $currentdata->forcelogin = (bool) get_config('core', 'forcelogin');
    $currentdata->guestloginbutton = get_config('core', 'guestloginbutton') !== '0';
    $currentdata->passwordpolicy = (bool) get_config('core', 'passwordpolicy');
    $currentdata->minpasswordlength = get_config('core', 'minpasswordlength') ?: 8;
    $currentdata->minpassworddigits = get_config('core', 'minpassworddigits');
    $currentdata->minpassworddigits = ($currentdata->minpassworddigits === false) ? 1 : (int) $currentdata->minpassworddigits;
    $currentdata->minpasswordlower = get_config('core', 'minpasswordlower');
    $currentdata->minpasswordlower = ($currentdata->minpasswordlower === false) ? 1 : (int) $currentdata->minpasswordlower;
    $currentdata->minpasswordupper = get_config('core', 'minpasswordupper');
    $currentdata->minpasswordupper = ($currentdata->minpasswordupper === false) ? 1 : (int) $currentdata->minpasswordupper;
    $currentdata->minpasswordnonalphanum = get_config('core', 'minpasswordnonalphanum');
    $currentdata->minpasswordnonalphanum = ($currentdata->minpasswordnonalphanum === false)
        ? 1 : (int) $currentdata->minpasswordnonalphanum;
    $currentdata->maxconsecutiveidentchars = get_config('core', 'maxconsecutiveidentchars');
    $currentdata->maxconsecutiveidentchars = ($currentdata->maxconsecutiveidentchars === false)
        ? 0 : (int) $currentdata->maxconsecutiveidentchars;
    $currentdata->lockoutthreshold = get_config('core', 'lockoutthreshold') ?: 10;
    $currentdata->displayloginfailures = (bool) get_config('core', 'displayloginfailures');
    $currentdata->allowframembedding = (bool) get_config('core', 'allowframembedding');
    $currentdata->sitepolicy = get_config('core', 'sitepolicy') ?: '';
    $currentdata->sitepolicyhandler_usetool = (get_config('core', 'sitepolicyhandler') === 'tool_policy');
    $mform->set_data($currentdata);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('tile_privacy_title', 'local_easyadmin'));
echo html_writer::tag('p', get_string('securityintro', 'local_easyadmin'), ['class' => 'text-muted']);
$mform->display();
echo $OUTPUT->footer();
