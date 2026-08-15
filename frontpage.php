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
 * Vereinfachte Startseiten-Einstellungen: Name, Kurzbezeichnung,
 * Informationen zur Website, Anzeige vor/nach der Anmeldung.
 *
 * Bildet die wichtigsten Felder von Website-Administration > Allgemein >
 * Startseite ab.
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/adminlib.php');
// Wird benötigt für die FRONTPAGE*-Konstanten UND für course_get_format(),
// das admin_setting_sitesettext::write_setting() intern aufruft. Ohne diese
// Zeile schlägt sowohl die Optionsliste als auch das Speichern fehl.
require_once($CFG->dirroot . '/course/lib.php');
require_once(__DIR__ . '/lib.php');

require_login();

$context = context_system::instance();
require_capability('local/easyadmin:managenews', $context);

$PAGE->set_url(new moodle_url('/local/easyadmin/frontpage.php'));
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('tile_news_title', 'local_easyadmin'));
$PAGE->set_heading(get_string('tile_news_title', 'local_easyadmin'));
$PAGE->navbar->add(
    get_string('dashboardtitle', 'local_easyadmin'),
    new moodle_url('/local/easyadmin/index.php')
);
$PAGE->navbar->add(get_string('tile_news_title', 'local_easyadmin'));

$returnurl = new moodle_url('/local/easyadmin/frontpage.php');
$dashboardurl = new moodle_url('/local/easyadmin/index.php');

$mform = new \local_easyadmin\form\frontpage_form();

if ($mform->is_cancelled()) {
    redirect($dashboardurl);
} else if ($data = $mform->get_data()) {
    global $SITE, $DB;

    // Name/Kurzbezeichnung/Beschreibung der Website: bewusst NICHT über
    // update_course(), da diese Funktion für reguläre Kurse gedacht ist und
    // zusätzliche Validierungen (Kategorie, Format etc.) durchführt, die für
    // den speziellen Startseiten-Pseudokurs (SITEID) zu einem "Ungültiger
    // Kurs"-Fehler führen können. Stattdessen dieselben dedizierten
    // Core-Klassen wie Website-Administration > Allgemein > Startseite
    // selbst: sie schreiben gezielt nur das jeweilige Feld in die
    // Kurs-Tabelle, inklusive korrektem Cache-Reset.
    $fullnamesetting = new admin_setting_sitesettext('fullname', '', '', null);
    $fullnamesetting->write_setting($data->fullname);

    $shortnamesetting = new admin_setting_sitesettext('shortname', '', '', null);
    $shortnamesetting->write_setting($data->shortname);

    $summarysetting = new admin_setting_special_frontpagedesc();
    $summarysetting->write_setting($data->summary);

    if (!empty($data->summary_showblock)) {
        local_easyadmin_ensure_frontpage_summary_block();
    }

    // Anzeige vor/nach der Anmeldung: echte Moodle-Kerneinstellungen, kein
    // lokaler Konfigurationswert dieses Plugins.
    set_config('frontpage', $data->frontpage);
    set_config('frontpageloggedin', $data->frontpageloggedin);

    redirect(
        $returnurl,
        get_string('frontpagesaved', 'local_easyadmin'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
} else {
    global $SITE, $DB;

    $currentdata = new stdClass();
    $currentdata->fullname = $SITE->fullname;
    $currentdata->shortname = $SITE->shortname;
    $currentdata->summary = $SITE->summary;

    $currentdata->summary_showblock = $DB->record_exists('block_instances', [
        'blockname' => 'course_summary',
        'parentcontextid' => $context->id,
        'pagetypepattern' => 'site-index',
    ]);

    // Nur-Bestpraxis-Vorbelegung: Falls bisher mehrere Elemente kombiniert
    // waren (kommagetrennt), wird hier nur das erste als aktuelle Auswahl
    // vorbelegt - siehe Hinweistext im Formular. Ein tatsächlich leerer Wert
    // wird korrekt als "Keine Anzeige" übernommen, statt einen Fallback-Wert
    // vorzutäuschen, der nicht der echten aktuellen Einstellung entspricht.
    $frontpageraw = $CFG->frontpage ?? '';
    if ($frontpageraw === '') {
        $currentdata->frontpage = '';
    } else {
        $frontpageparts = explode(',', $frontpageraw);
        $currentdata->frontpage = (string) reset($frontpageparts);
    }

    $frontpageloggedinraw = $CFG->frontpageloggedin ?? '';
    if ($frontpageloggedinraw === '') {
        $currentdata->frontpageloggedin = '';
    } else {
        $frontpageloggedinparts = explode(',', $frontpageloggedinraw);
        $currentdata->frontpageloggedin = (string) reset($frontpageloggedinparts);
    }

    $mform->set_data($currentdata);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('tile_news_title', 'local_easyadmin'));
echo html_writer::tag('p', get_string('frontpageintro', 'local_easyadmin'), ['class' => 'text-muted']);
$mform->display();
echo $OUTPUT->footer();
