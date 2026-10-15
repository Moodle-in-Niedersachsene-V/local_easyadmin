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
 * Design-Einstellungen: Logo, Hintergrundbild/-farbe, Textfarbe/-größe.
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/formslib.php');
require_once(__DIR__ . '/lib.php');

require_login();

$context = context_system::instance();
require_capability('local/easyadmin:managedesign', $context);

$PAGE->set_url(new moodle_url('/local/easyadmin/design.php'));
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('tile_design_title', 'local_easyadmin'));
$PAGE->set_heading(get_string('tile_design_title', 'local_easyadmin'));
$PAGE->navbar->add(
    get_string('dashboardtitle', 'local_easyadmin'),
    new moodle_url('/local/easyadmin/index.php')
);
$PAGE->navbar->add(get_string('tile_design_title', 'local_easyadmin'));

$returnurl = new moodle_url('/local/easyadmin/design.php');
$dashboardurl = new moodle_url('/local/easyadmin/index.php');

$mform = new \local_easyadmin\form\design_form();

// Bestehende Dateien in die Draft-Areas laden, damit sie im Filemanager
// vorausgefüllt erscheinen.
$logodraftitemid = file_get_submitted_draft_itemid('logo');
file_prepare_draft_area(
    $logodraftitemid,
    $context->id,
    'core_admin',
    'logo',
    0,
    ['subdirs' => 0, 'maxfiles' => 1]
);

$backgrounddraftitemid = file_get_submitted_draft_itemid('backgroundimage');
file_prepare_draft_area(
    $backgrounddraftitemid,
    $context->id,
    'local_easyadmin',
    'backgroundimage',
    0,
    ['subdirs' => 0, 'maxfiles' => 1]
);

$popupimagedraftitemid = file_get_submitted_draft_itemid('welcomepopup_image');
file_prepare_draft_area(
    $popupimagedraftitemid,
    $context->id,
    'local_easyadmin',
    'welcomepopupimage',
    0,
    ['subdirs' => 0, 'maxfiles' => 1]
);

$loginimagedraftitemid = file_get_submitted_draft_itemid('loginpage_image');
file_prepare_draft_area(
    $loginimagedraftitemid,
    $context->id,
    'local_easyadmin',
    'loginpageimage',
    0,
    ['subdirs' => 0, 'maxfiles' => LOCAL_EASYADMIN_LOGINPAGE_MAXIMAGES]
);

if ($mform->is_cancelled()) {
    redirect($dashboardurl);
} else if ($data = $mform->get_data()) {
    // Logo: nutzt Moodles eigenen Mechanismus für das echte Systemlogo
    // (wirkt im Header, auf der Anmeldeseite und in E-Mails/PDFs).
    //
    // Der Header/die Navigationsleiste (Boost) verwendet ein EIGENES,
    // kompaktes Logo ('logocompact'), getrennt vom "großen" Logo. Da
    // admin_setting_configstoredfile::write_setting() den übergebenen
    // Draft-Bereich beim Verarbeiten leert, muss die Datei VOR dem ersten
    // Speichervorgang dupliziert werden - sonst bliebe logocompact leer.
    $logocompactdraftitemid = local_easyadmin_clone_draft_area((int) $data->logo);

    $logosetting = new admin_setting_configstoredfile('core_admin/logo', '', '', 'logo');
    $logosetting->write_setting($data->logo);

    $logocompactsetting = new admin_setting_configstoredfile('core_admin/logocompact', '', '', 'logocompact');
    $logocompactsetting->write_setting($logocompactdraftitemid);

    // Hintergrundbild: eigener, isolierter Dateibereich des Plugins.
    file_save_draft_area_files(
        $data->backgroundimage,
        $context->id,
        'local_easyadmin',
        'backgroundimage',
        0,
        ['subdirs' => 0, 'maxfiles' => 1]
    );

    // Popup-Bild: ebenfalls eigener, isolierter Dateibereich.
    // Ist das Popup deaktiviert, blendet hideIf() den Filemanager aus und
    // deaktiviert ihn – deaktivierte Felder werden vom Browser NICHT
    // abgeschickt, also fehlt $data->welcomepopup_image. Fallback auf den
    // oben vorbereiteten Draft-Bereich: erhält vorhandene Dateien unverändert.
    file_save_draft_area_files(
        $data->welcomepopup_image ?? $popupimagedraftitemid,
        $context->id,
        'local_easyadmin',
        'welcomepopupimage',
        0,
        ['subdirs' => 0, 'maxfiles' => 1]
    );

    // Login-Seiten-Bild: ebenfalls eigener, isolierter Dateibereich.
    // Gleiche Absicherung wie beim Popup-Bild (kann per hideIf ausgeblendet
    // und damit nicht mitgesendet werden).
    file_save_draft_area_files(
        $data->loginpage_image ?? $loginimagedraftitemid,
        $context->id,
        'local_easyadmin',
        'loginpageimage',
        0,
        ['subdirs' => 0, 'maxfiles' => LOCAL_EASYADMIN_LOGINPAGE_MAXIMAGES]
    );

    // Darstellung der Anmeldeseite. Alle Werte werden gegen feste Listen
    // geprüft, damit keine beliebigen Eingaben in das Seiten-CSS gelangen.
    set_config('loginpage_hidepanel', !empty($data->loginpage_hidepanel) ? 1 : 0, 'local_easyadmin');
    set_config('loginpage_fullbackground', !empty($data->loginpage_fullbackground) ? 1 : 0, 'local_easyadmin');
    $interval = (int) ($data->loginpage_interval ?? 10);
    set_config(
        'loginpage_interval',
        in_array($interval, LOCAL_EASYADMIN_LOGINPAGE_INTERVALS, true) ? $interval : 10,
        'local_easyadmin'
    );
    $formposition = (string) ($data->loginpage_formposition ?? 'right');
    set_config(
        'loginpage_formposition',
        in_array($formposition, LOCAL_EASYADMIN_LOGINPAGE_POSITIONS, true) ? $formposition : 'right',
        'local_easyadmin'
    );

    // Hinweistext der Anmeldeseite: Dieser Wert gehört NICHT dem Plugin, sondern
    // ist die Moodle-Kerneinstellung auth_instructions (config-Tabelle, ohne
    // Plugin-Präfix). set_config() ohne dritten Parameter schreibt genau dorthin,
    // wodurch die Änderung sofort auf der echten Anmeldeseite sichtbar wird und
    // in der Website-Administration identisch erscheint.
    $instructions = '';
    if (isset($data->loginpage_instructions['text'])) {
        // Der Aufruf clean_text() entfernt Skripte/Event-Attribute. Wichtig,
        // weil dieser Text auf der öffentlich erreichbaren Anmeldeseite
        // ausgegeben wird.
        $instructions = clean_text((string) $data->loginpage_instructions['text'], FORMAT_HTML);
    }

    // Ein "geleerter" Editor liefert je nach Editor Reste wie <p><br></p>.
    // Ohne diese Normalisierung bliebe auf der Anmeldeseite eine leere graue
    // Box stehen, statt dass Moodle wieder seinen Standardtext anzeigt.
    if (trim(html_to_text($instructions, 0, false)) === '' && stripos($instructions, '<img') === false) {
        $instructions = '';
    }

    set_config('auth_instructions', $instructions);

    // Farb-/Textwerte wurden bereits serverseitig gegen ein strenges
    // #RRGGBB-Muster validiert (siehe design_form::validation()).
    set_config('backgroundcolor', $data->backgroundcolor, 'local_easyadmin');
    set_config('textcolor', $data->textcolor, 'local_easyadmin');
    set_config('textsize', $data->textsize, 'local_easyadmin');

    // Maximale Seiten-/Inhaltsbreite. Nur bekannte, sichere Werte zulassen -
    // so kann über dieses Feld kein beliebiges CSS in den Seitenkopf gelangen.
    $allowedwidths = ['', '1200px', '1400px', '100%'];
    $pagewidth = $data->pagewidth ?? '';
    set_config('page_width', in_array($pagewidth, $allowedwidths, true) ? $pagewidth : '', 'local_easyadmin');

    set_config('welcomepopup_enabled', !empty($data->welcomepopup_enabled) ? 1 : 0, 'local_easyadmin');

    // Die folgenden Popup-Felder werden per hideIf() ausgeblendet, sobald das
    // Popup deaktiviert ist. Ausgeblendete (= deaktivierte) Felder sendet der
    // Browser nicht mit, deshalb können die Eigenschaften fehlen. Der Null-
    // Coalescing-Operator (??) fängt das ab. Bewusst wird auf den zuletzt
    // gespeicherten Wert zurückgefallen (nicht auf ''), damit ein Speichern bei
    // deaktiviertem Popup die vorhandenen Texte nicht ungewollt löscht.
    set_config(
        'welcomepopup_title',
        $data->welcomepopup_title ?? get_config('local_easyadmin', 'welcomepopup_title'),
        'local_easyadmin'
    );
    set_config(
        'welcomepopup_message',
        $data->welcomepopup_message ?? get_config('local_easyadmin', 'welcomepopup_message'),
        'local_easyadmin'
    );
    set_config(
        'welcomepopup_buttontext',
        $data->welcomepopup_buttontext ?? get_config('local_easyadmin', 'welcomepopup_buttontext'),
        'local_easyadmin'
    );
    set_config(
        'welcomepopup_buttonurl',
        clean_param($data->welcomepopup_buttonurl ?? get_config('local_easyadmin', 'welcomepopup_buttonurl'), PARAM_URL),
        'local_easyadmin'
    );
    set_config(
        'welcomepopup_autoclose',
        (int) ($data->welcomepopup_autoclose ?? get_config('local_easyadmin', 'welcomepopup_autoclose')),
        'local_easyadmin'
    );

    redirect(
        $returnurl,
        get_string('designsaved', 'local_easyadmin'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
} else {
    $currentdata = new stdClass();
    $currentdata->logo = $logodraftitemid;
    $currentdata->backgroundimage = $backgrounddraftitemid;
    $currentdata->welcomepopup_image = $popupimagedraftitemid;
    $currentdata->loginpage_hidepanel = (int) !empty(get_config('local_easyadmin', 'loginpage_hidepanel'));
    $currentdata->loginpage_fullbackground = (int) !empty(get_config('local_easyadmin', 'loginpage_fullbackground'));
    $savedinterval = get_config('local_easyadmin', 'loginpage_interval');
    $currentdata->loginpage_interval = ($savedinterval === false || $savedinterval === '') ? 10 : (int) $savedinterval;
    $currentdata->loginpage_formposition = get_config('local_easyadmin', 'loginpage_formposition') ?: 'right';
    $currentdata->loginpage_image = $loginimagedraftitemid;

    // Aktuellen Wert der Moodle-Kerneinstellung auth_instructions vorbefüllen.
    // get_config('moodle', ...) liest die Kern-Konfiguration (identisch zu
    // $CFG->auth_instructions), liefert bei fehlendem Eintrag false.
    $currentdata->loginpage_instructions = [
        'text' => (string) get_config('moodle', 'auth_instructions'),
        'format' => FORMAT_HTML,
    ];
    $currentdata->backgroundcolor = get_config('local_easyadmin', 'backgroundcolor') ?: '#ffffff';
    $currentdata->textcolor = get_config('local_easyadmin', 'textcolor') ?: '#000000';
    $currentdata->textsize = get_config('local_easyadmin', 'textsize') ?: 'normal';

    // Die Funktion get_config() liefert false, wenn noch nichts gesetzt ist -
    // dann leerer String (= Moodle-Standardbreite, keine Überschreibung).
    $currentdata->pagewidth = get_config('local_easyadmin', 'page_width') ?: '';
    $currentdata->welcomepopup_enabled = (bool) get_config('local_easyadmin', 'welcomepopup_enabled');
    $currentdata->welcomepopup_title = get_config('local_easyadmin', 'welcomepopup_title')
        ?: get_string('welcomepopup_title_default', 'local_easyadmin');
    $currentdata->welcomepopup_message = get_config('local_easyadmin', 'welcomepopup_message')
        ?: get_string('welcomepopup_message_default', 'local_easyadmin');
    $currentdata->welcomepopup_buttontext = get_config('local_easyadmin', 'welcomepopup_buttontext')
        ?: get_string('welcomepopup_buttontext_default', 'local_easyadmin');
    $currentdata->welcomepopup_buttonurl = get_config('local_easyadmin', 'welcomepopup_buttonurl') ?: '/login/index.php';
    $autoclosevalue = get_config('local_easyadmin', 'welcomepopup_autoclose');
    $currentdata->welcomepopup_autoclose = ($autoclosevalue === false || $autoclosevalue === '') ? 15 : (int) $autoclosevalue;
    $mform->set_data($currentdata);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('tile_design_title', 'local_easyadmin'));
echo html_writer::tag('p', get_string('designintro', 'local_easyadmin'), ['class' => 'text-muted']);
$mform->display();
echo $OUTPUT->footer();
