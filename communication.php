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
 * Vereinfachte Kommunikations-Einstellungen.
 *
 * Bündelt kommunikationsbezogene Kerneinstellungen (aktuell: das Moodle-interne
 * Mitteilungssystem). Der Bereich ist so aufgebaut, dass später der
 * E-Mail-Bereich des separaten Plugins local_mail ergänzt werden kann.
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');

require_login();

$context = context_system::instance();
require_capability('local/easyadmin:managecommunication', $context);

$PAGE->set_url(new moodle_url('/local/easyadmin/communication.php'));
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('tile_communication_title', 'local_easyadmin'));
$PAGE->set_heading(get_string('tile_communication_title', 'local_easyadmin'));
$PAGE->navbar->add(
    get_string('dashboardtitle', 'local_easyadmin'),
    new moodle_url('/local/easyadmin/index.php')
);
$PAGE->navbar->add(get_string('tile_communication_title', 'local_easyadmin'));

$returnurl = new moodle_url('/local/easyadmin/communication.php');
$dashboardurl = new moodle_url('/local/easyadmin/index.php');

$mform = new \local_easyadmin\form\communication_form();

if ($mform->is_cancelled()) {
    redirect($dashboardurl);
} else if ($data = $mform->get_data()) {
    // Die Einstellung messaging ist eine echte Moodle-Kerneinstellung (kein
    // Plugin-Namespace), identisch zu Website-Administration > Allgemein >
    // Erweiterte Einstellungen > "Mitteilungssystem aktivieren". set_config()
    // ohne dritten Parameter schreibt genau dorthin ($CFG->messaging), die
    // Änderung wirkt sofort.
    set_config('messaging', !empty($data->messaging) ? 1 : 0);

    // Hinweis: Das primäre Mail-Plugin local_mail hat keinen speicherbaren
    // Aktivierungswert (kein 'enabled'-Schlüssel; Zugriff via Capability). Sein
    // Abschnitt ist reine Info + Link, daher ist hier für local_mail nichts zu
    // schreiben.

    // Optionaler Zusatz: local_emailclient erzwingt seinen 'enableplugin'-
    // Schlüssel selbst (page_helper) - der Schalter wirkt direkt auf Navigation
    // und Zugriff. Nur schreiben, wenn dieses Zusatz-Plugin installiert ist.
    if (\local_easyadmin\form\communication_form::emailclient_is_available()) {
        set_config('enableplugin', !empty($data->emailclient_enabled) ? 1 : 0, 'local_emailclient');
    }

    redirect(
        $returnurl,
        get_string('communicationsaved', 'local_easyadmin'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
} else {
    $currentdata = new stdClass();

    // Aktuellen Zustand der Kerneinstellung messaging vorbefüllen.
    // get_config('core', 'messaging') entspricht $CFG->messaging und liefert
    // bei fehlendem Eintrag false. Moodle liefert Standard-Installationen
    // i. d. R. mit aktiviertem Mitteilungssystem aus - daher ist der sinnvolle
    // Default hier "an" (true), wenn noch nichts explizit gesetzt wurde.
    $messaging = get_config('core', 'messaging');
    $currentdata->messaging = ($messaging === false) ? true : (bool) $messaging;

    // Primäres Mail-Plugin local_mail: keine Werte vorzubefüllen (reine Info +
    // Link, kein Aktivierungsschalter).

    // Optionaler Zusatz local_emailclient: Aktivierungsstatus vorbefüllen (nur
    // wenn installiert). Standardwert des Plugins ist "aktiviert" (1), daher bei
    // noch nicht gesetztem Wert true.
    if (\local_easyadmin\form\communication_form::emailclient_is_available()) {
        $enableplugin = get_config('local_emailclient', 'enableplugin');
        $currentdata->emailclient_enabled = ($enableplugin === false) ? true : (bool) $enableplugin;
    }

    $mform->set_data($currentdata);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('tile_communication_title', 'local_easyadmin'));
echo html_writer::tag('p', get_string('communicationintro', 'local_easyadmin'), ['class' => 'text-muted']);
$mform->display();
echo $OUTPUT->footer();
