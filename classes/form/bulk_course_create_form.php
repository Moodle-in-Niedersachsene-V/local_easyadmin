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
 * Formular zum gleichzeitigen Anlegen mehrerer Kurse aus einer Liste.
 *
 * Format je Zeile: Vollständiger Name;Kurzbezeichnung
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class bulk_course_create_form extends \moodleform {
    /**
     * Baut das Formular zum Anlegen mehrerer Kurse auf.
     *
     * @return void
     */
    protected function definition() {
        global $DB;

        $mform = $this->_form;

        $categoryoptions = \core_course_category::make_categories_list('moodle/course:create');
        $mform->addElement('select', 'category', get_string('coursecategory_easyadmin', 'local_easyadmin'), $categoryoptions);

        $mform->addElement(
            'textarea',
            'entries',
            get_string('bulkcourse_entries_easyadmin', 'local_easyadmin'),
            ['rows' => 8, 'cols' => 50]
        );
        $mform->setType('entries', PARAM_RAW);
        $mform->addRule('entries', get_string('error_required', 'local_easyadmin'), 'required', null, 'client');
        $mform->addHelpButton('entries', 'bulkcourse_entries_easyadmin', 'local_easyadmin');

        $mform->addElement('header', 'enrolheader', get_string('enrolheader_easyadmin', 'local_easyadmin'));
        $mform->setExpanded('enrolheader', true);

        $mform->addElement(
            'static',
            'enrolheader_notice',
            '',
            get_string('bulkcourse_enrolnotice_easyadmin', 'local_easyadmin')
        );

        $mform->addElement('text', 'teacheridentifier', get_string('teacheridentifier_easyadmin', 'local_easyadmin'));
        $mform->setType('teacheridentifier', PARAM_RAW);
        $mform->addHelpButton('teacheridentifier', 'teacheridentifier_easyadmin', 'local_easyadmin');

        $cohortoptions = [0 => get_string('cohortoption_none_easyadmin', 'local_easyadmin')];
        $cohorts = $DB->get_records('cohort', null, 'name ASC', 'id, name');
        foreach ($cohorts as $cohort) {
            $cohortoptions[$cohort->id] = format_string($cohort->name);
        }
        $mform->addElement('select', 'cohortid', get_string('cohortenrol_easyadmin', 'local_easyadmin'), $cohortoptions);
        $mform->addHelpButton('cohortid', 'cohortenrol_easyadmin', 'local_easyadmin');

        $this->add_action_buttons(false, get_string('bulkcourse_create_button', 'local_easyadmin'));
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

        $lines = array_filter(array_map('trim', explode("\n", str_replace("\r", '', $data['entries']))));
        if (empty($lines)) {
            $errors['entries'] = get_string('error_required', 'local_easyadmin');
        } else if (count($lines) > 200) {
            $errors['entries'] = get_string('error_bulkcategory_toomany', 'local_easyadmin');
        }

        $teacheridentifier = trim($data['teacheridentifier']);
        if ($teacheridentifier !== '' && !local_easyadmin_find_user_by_identifier($teacheridentifier)) {
            $errors['teacheridentifier'] = get_string('error_teachernotfound_easyadmin', 'local_easyadmin');
        }

        return $errors;
    }
}
