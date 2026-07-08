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
 * Eigenständiges Formular für die Log-Aufbewahrungsdauer, damit es sich
 * inhaltlich im Berichte-Bereich platzieren lässt (eigener Speichern-Button,
 * getrennt vom Debug-Modus-Formular).
 *
 * Bewusst OHNE die Option "Nie löschen": Aus Datenschutzgründen soll diese
 * Oberfläche Admins nicht dazu verleiten, Protokolldaten dauerhaft
 * aufzubewahren. Ist aktuell dennoch "Nie löschen" hinterlegt (z. B. weil
 * das der Moodle-Standard ist oder es zuvor manuell so gesetzt wurde), wird
 * dies transparent als eigene, deutlich gekennzeichnete Option angezeigt,
 * statt es stillschweigend zu verändern oder zu verstecken.
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class logretention_form extends \moodleform {

    /**
     * @return void
     */
    protected function definition() {
        $mform = $this->_form;

        $loglifetimeoptions = [
            30 => get_string('logretention_days', 'local_easyadmin', 30),
            50 => get_string('logretention_days', 'local_easyadmin', 50),
            60 => get_string('logretention_days', 'local_easyadmin', 60),
        ];

        $currentloglifetime = (int) ($this->_customdata['currentloglifetime'] ?? 30);

        if (!array_key_exists($currentloglifetime, $loglifetimeoptions)) {
            if ($currentloglifetime === 0) {
                // Aktuell "Nie löschen" - bewusst nicht als reguläre Option
                // anbieten, aber transparent anzeigen und zur Änderung anregen.
                $loglifetimeoptions[0] = get_string('logretention_never_current', 'local_easyadmin');
            } else {
                $loglifetimeoptions[$currentloglifetime] = get_string(
                    'logretention_current',
                    'local_easyadmin',
                    $currentloglifetime
                );
            }
        }

        $mform->addElement(
            'select',
            'loglifetime',
            get_string('loglifetime_easyadmin', 'local_easyadmin'),
            $loglifetimeoptions
        );
        $mform->addHelpButton('loglifetime', 'loglifetime_easyadmin', 'local_easyadmin');

        $this->add_action_buttons(false, get_string('savechanges'));
    }
}
