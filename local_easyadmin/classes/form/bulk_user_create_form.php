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
 * Formular zum gleichzeitigen Anlegen mehrerer manueller Nutzerkonten aus
 * einer Liste. Passwörter werden automatisch generiert (keine
 * Admin-Vergabe über diesen Weg - dafür bewusst nur das Einzelformular).
 *
 * Format je Zeile: Vorname;Nachname;E-Mail;Anmeldename
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class bulk_user_create_form extends \moodleform {

    /**
     * @return void
     */
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement(
            'static',
            'manualaccount_notice',
            '',
            \html_writer::div(get_string('manualaccount_notice', 'local_easyadmin'), 'alert alert-info')
        );

        $mform->addElement(
            'textarea',
            'entries',
            get_string('bulkuser_entries_easyadmin', 'local_easyadmin'),
            ['rows' => 8, 'cols' => 60]
        );
        $mform->setType('entries', PARAM_RAW);
        $mform->addRule('entries', get_string('error_required', 'local_easyadmin'), 'required', null, 'client');
        $mform->addHelpButton('entries', 'bulkuser_entries_easyadmin', 'local_easyadmin');

        $mform->addElement(
            'advcheckbox',
            'forcepasswordchange',
            get_string('forcepasswordchange_easyadmin', 'local_easyadmin')
        );
        $mform->setDefault('forcepasswordchange', 1);

        $this->add_action_buttons(false, get_string('bulkuser_create_button', 'local_easyadmin'));
    }

    /**
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $lines = array_filter(array_map('trim', explode("\n", str_replace("\r", '', $data['entries']))));
        if (empty($lines)) {
            $errors['entries'] = get_string('error_required', 'local_easyadmin');
        } else if (count($lines) > 200) {
            $errors['entries'] = get_string('error_bulkcategory_toomany', 'local_easyadmin');
        }

        return $errors;
    }
}
