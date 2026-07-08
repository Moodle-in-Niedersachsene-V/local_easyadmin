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
 * Formular zum Anlegen einer Kohorte ("globale Gruppe").
 *
 * Kohorten sind Moodles kursübergreifendes Gruppierungskonzept - im
 * Unterschied zu normalen Gruppen, die immer an einen einzelnen Kurs
 * gebunden sind.
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cohort_create_form extends \moodleform {

    /**
     * @return void
     */
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('text', 'name', get_string('cohortname_easyadmin', 'local_easyadmin'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('error_required', 'local_easyadmin'), 'required', null, 'client');

        $mform->addElement('text', 'idnumber', get_string('cohortidnumber_easyadmin', 'local_easyadmin'));
        $mform->setType('idnumber', PARAM_TEXT);
        $mform->addHelpButton('idnumber', 'cohortidnumber_easyadmin', 'local_easyadmin');

        $mform->addElement(
            'textarea',
            'description',
            get_string('cohortdescription_easyadmin', 'local_easyadmin'),
            ['rows' => 3, 'cols' => 50]
        );
        $mform->setType('description', PARAM_TEXT);

        $this->add_action_buttons(false, get_string('createcohort_easyadmin', 'local_easyadmin'));
    }

    /**
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);

        if (trim((string) $data['idnumber']) !== '' && $DB->record_exists('cohort', ['idnumber' => $data['idnumber']])) {
            $errors['idnumber'] = get_string('error_cohortidnumbertaken', 'local_easyadmin');
        }

        return $errors;
    }
}
