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

namespace local_easyadmin\form;

defined('MOODLE_INTERNAL') || die();

require_once($GLOBALS['CFG']->libdir . '/formslib.php');

/**
 * Formular für den vereinfachten Kommunikationsbereich.
 *
 * Bündelt kommunikationsbezogene Einstellungen an einem Ort: das Moodle-interne
 * Mitteilungssystem ($CFG->messaging), das primäre Mail-Plugin local_mail
 * (Hauptsystem, offizielles Niedersachsen-Repository) sowie - rein optional -
 * das Zusatz-Plugin local_emailclient. Das Formular ist in getrennte Abschnitte
 * (Header/Fieldsets) gegliedert. Jeder Mail-Abschnitt wird nur eingehängt, wenn
 * das jeweilige Plugin installiert ist (strikte Prüfung via
 * \core_component::get_plugin_directory()); es gibt keine harte Abhängigkeit.
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class communication_form extends \moodleform {
    /**
     * Baut das Formular für die Kommunikationseinstellungen auf.
     *
     * @return void
     */
    protected function definition() {
        $mform = $this->_form;

        // Abschnitt: Mitteilungen (Moodle-Kern).
        $this->add_messaging_section($mform);

        // Primärer Abschnitt: E-Mail (local_mail).
        // local_mail (Briefumschlag-Icon im Header) ist das Hauptsystem und das
        // einzige offiziell im Niedersachsen-Repository geführte Mail-Plugin.
        // Der Abschnitt wird nur eingehängt, wenn local_mail installiert ist -
        // es gibt KEINE harte Abhängigkeit, die Kachel funktioniert auch ohne.
        if (self::localmail_is_available()) {
            $this->add_localmail_section($mform);
        }

        // Optionaler Zusatz: E-Mail-Client (local_emailclient).
        // Rein optional: Nur wenn dieses Zusatz-Plugin zufällig installiert ist,
        // erscheint der zusätzliche Abschnitt mit seinem Aktivierungsschalter.
        // Fehlt es, passiert nichts - keine Abhängigkeit, kein Fehler.
        if (self::emailclient_is_available()) {
            $this->add_emailclient_section($mform);
        }

        $this->add_action_buttons(true, get_string('savechanges'));
    }

    /**
     * Abschnitt "Mitteilungen": steuert die Moodle-Kerneinstellung messaging.
     *
     * @param \MoodleQuickForm $mform
     * @return void
     */
    protected function add_messaging_section(\MoodleQuickForm $mform): void {
        $mform->addElement('header', 'messagingheader', get_string('messagingheader', 'local_easyadmin'));
        $mform->setExpanded('messagingheader', true);

        $mform->addElement('advcheckbox', 'messaging', get_string('messaging_easyadmin', 'local_easyadmin'));
        $mform->addHelpButton('messaging', 'messaging_easyadmin', 'local_easyadmin');
    }

    /**
     * Primärer Abschnitt "E-Mail" für das Hauptsystem local_mail.
     *
     * local_mail besitzt bewusst KEINEN Konfigurationsschalter zum Ein-/
     * Ausschalten; der Zugriff wird über die Moodle-Rollenverwaltung
     * (Capability local/mail:usemail) gesteuert. Der Abschnitt zeigt daher nur
     * einen kurzen Status und den Link zur vollständigen Einstellungsseite -
     * bewusst keine Checkbox, die ohne echten Schlüssel nichts bewirken würde.
     *
     * @param \MoodleQuickForm $mform
     * @return void
     */
    protected function add_localmail_section(\MoodleQuickForm $mform): void {
        $mform->addElement('header', 'localmailheader', get_string('localmailheader', 'local_easyadmin'));
        $mform->setExpanded('localmailheader', true);

        $mform->addElement(
            'static',
            'localmail_status',
            '',
            get_string('localmail_status_easyadmin', 'local_easyadmin')
        );

        $fulllink = \html_writer::link(
            new \moodle_url('/admin/settings.php', ['section' => 'local_mail']),
            get_string('localmail_fullsettings_easyadmin', 'local_easyadmin')
        );
        $mform->addElement('static', 'localmail_fulllink', '', $fulllink);
    }

    /**
     * Optionaler Zusatz-Abschnitt für das Plugin local_emailclient.
     *
     * Ein einfacher An/Aus-Schalter plus Verweis auf die vollständige
     * Einstellungsseite. Der Schalter liest/schreibt den ECHTEN, vom Plugin
     * selbst erzwungenen Konfigurationsschlüssel 'enableplugin'; local_emailclient
     * prüft diesen Wert in page_helper und blendet bei 0 Navigation/Zugriff aus.
     * Wird nur eingehängt, wenn local_emailclient installiert ist.
     *
     * @param \MoodleQuickForm $mform
     * @return void
     */
    protected function add_emailclient_section(\MoodleQuickForm $mform): void {
        $mform->addElement('header', 'emailclientheader', get_string('emailclientheader', 'local_easyadmin'));
        $mform->setExpanded('emailclientheader', true);

        $mform->addElement(
            'advcheckbox',
            'emailclient_enabled',
            get_string('emailclient_enabled_easyadmin', 'local_easyadmin')
        );
        $mform->addHelpButton('emailclient_enabled', 'emailclient_enabled_easyadmin', 'local_easyadmin');

        // Link auf die vollständige Einstellungsseite des E-Mail-Clients.
        $fulllink = \html_writer::link(
            new \moodle_url('/admin/settings.php', ['section' => 'local_emailclient']),
            get_string('emailclient_fullsettings_easyadmin', 'local_easyadmin')
        );
        $mform->addElement('static', 'emailclient_fulllink', '', $fulllink);
    }

    /**
     * Ist das Hauptsystem local_mail installiert und einsatzbereit?
     *
     * Strikte Existenzprüfung über \core_component::get_plugin_directory():
     * (1) liegt der Plugin-Code auf der Platte, und (2) ist die Installation
     * abgeschlossen (Version in der DB hinterlegt). Verhindert eine harte
     * Abhängigkeit - fehlt local_mail, liefert die Methode sauber false.
     *
     * @return bool
     */
    public static function localmail_is_available(): bool {
        if (\core_component::get_plugin_directory('local', 'mail') === null) {
            return false;
        }
        return get_config('local_mail', 'version') !== false;
    }

    /**
     * Ist das optionale Zusatz-Plugin local_emailclient installiert und
     * einsatzbereit?
     *
     * Gleiche strikte Existenzprüfung wie bei local_mail. Fehlt das Plugin,
     * liefert die Methode false und der Zusatz-Abschnitt entfällt vollständig -
     * ohne Fehler und ohne Abhängigkeit.
     *
     * @return bool
     */
    public static function emailclient_is_available(): bool {
        if (\core_component::get_plugin_directory('local', 'emailclient') === null) {
            return false;
        }
        return get_config('local_emailclient', 'version') !== false;
    }
}
