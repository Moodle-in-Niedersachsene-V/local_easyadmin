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
 * Formular für die Einstellungen zur automatischen Löschung inaktiver
 * Nutzerkonten. Bewusst standardmäßig deaktiviert und mit einer festen
 * Mindestschwelle (LOCAL_EASYADMIN_INACTIVEDELETION_MINDAYS) abgesichert,
 * da diese Funktion ohne weiteres menschliches Zutun zur Ausführungszeit
 * läuft.
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class inactivedeletion_form extends \moodleform {
    /**
     * Baut das Formular für die Inaktivitäts-Löschregel auf.
     *
     * @return void
     */
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement(
            'static',
            'inactivedeletion_warning',
            '',
            \html_writer::div(get_string('inactivedeletion_warning', 'local_easyadmin'), 'alert alert-danger')
        );

        $mform->addElement(
            'advcheckbox',
            'inactivedeletion_enabled',
            get_string('inactivedeletion_enabled', 'local_easyadmin')
        );
        $mform->addHelpButton('inactivedeletion_enabled', 'inactivedeletion_enabled', 'local_easyadmin');

        $mform->addElement(
            'text',
            'inactivedeletion_days',
            get_string('inactivedeletion_days', 'local_easyadmin'),
            ['size' => 5]
        );
        $mform->setType('inactivedeletion_days', PARAM_INT);
        $mform->addHelpButton('inactivedeletion_days', 'inactivedeletion_days', 'local_easyadmin');

        $this->add_action_buttons(false, get_string('savechanges'));
    }

    /**
     * Validiert die eingegebenen Formulardaten serverseitig.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $days = (int) $data['inactivedeletion_days'];
        if ($days < LOCAL_EASYADMIN_INACTIVEDELETION_MINDAYS) {
            $errors['inactivedeletion_days'] = get_string(
                'error_inactivedeletion_mindays',
                'local_easyadmin',
                LOCAL_EASYADMIN_INACTIVEDELETION_MINDAYS
            );
        }

        return $errors;
    }
}
