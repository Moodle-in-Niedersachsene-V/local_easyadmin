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
 * Formular zum Aktivieren zusätzlicher Block-Bereiche im Inhaltsbereich
 * der Startseite und der Kurs-Hauptseite (oben und unten).
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class blocks_form extends \moodleform {
    /**
     * Baut das Formular mit den vier Schaltern auf.
     *
     * @return void
     */
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'frontpageheader', get_string('blocks_header_frontpage', 'local_easyadmin'));
        $this->add_area_checkbox('frontpage_top');
        $this->add_area_checkbox('frontpage_bottom');

        $mform->addElement('header', 'courseheader', get_string('blocks_header_course', 'local_easyadmin'));
        $this->add_area_checkbox('course_top');
        $this->add_area_checkbox('course_bottom');

        $this->add_action_buttons(false, get_string('savechanges'));
    }

    /**
     * Fügt einen Schalter mit Kontext-Hilfe für einen Bereich hinzu.
     *
     * @param string $area Bereichsschlüssel, z. B. frontpage_top
     * @return void
     */
    private function add_area_checkbox(string $area): void {
        $mform = $this->_form;
        $mform->addElement(
            'advcheckbox',
            'blocks_' . $area,
            get_string('blocks_' . $area, 'local_easyadmin'),
            get_string('blocks_enable', 'local_easyadmin')
        );
        $mform->setType('blocks_' . $area, PARAM_BOOL);
        $mform->addHelpButton('blocks_' . $area, 'blocks_' . $area, 'local_easyadmin');
    }
}
