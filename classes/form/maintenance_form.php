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
 * Formular für den Debug-Modus-Schalter im Wartungsbereich.
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class maintenance_form extends \moodleform {
    /**
     * Baut das Formular für den Debug-Modus-Schalter auf.
     *
     * @return void
     */
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement(
            'static',
            'debugmode_warning',
            '',
            \html_writer::div(
                get_string('debugmode_warning', 'local_easyadmin'),
                'alert alert-warning'
            )
        );

        $mform->addElement('advcheckbox', 'debugmode_enabled', get_string('debugmode_enabled', 'local_easyadmin'));
        $mform->addHelpButton('debugmode_enabled', 'debugmode_enabled', 'local_easyadmin');

        $this->add_action_buttons(true, get_string('savechanges'));
    }
}
