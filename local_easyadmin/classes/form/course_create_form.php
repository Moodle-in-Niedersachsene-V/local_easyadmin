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
 * Formular zum Anlegen eines einzelnen Kurses.
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_create_form extends \moodleform {

    /**
     * @return void
     */
    protected function definition() {
        global $DB;

        $mform = $this->_form;

        $mform->addElement('text', 'fullname', get_string('fullnamecourse'));
        $mform->setType('fullname', PARAM_TEXT);
        $mform->addRule('fullname', get_string('error_required', 'local_easyadmin'), 'required', null, 'client');

        $mform->addElement('text', 'shortname', get_string('shortnamecourse'));
        $mform->setType('shortname', PARAM_TEXT);
        $mform->addRule('shortname', get_string('error_required', 'local_easyadmin'), 'required', null, 'client');

        $categoryoptions = \core_course_category::make_categories_list('moodle/course:create');
        $mform->addElement('select', 'category', get_string('coursecategory_easyadmin', 'local_easyadmin'), $categoryoptions);

        $mform->addElement('advcheckbox', 'visible', get_string('coursevisible_easyadmin', 'local_easyadmin'));
        $mform->setDefault('visible', 1);

        $mform->addElement('header', 'enrolheader', get_string('enrolheader_easyadmin', 'local_easyadmin'));
        $mform->setExpanded('enrolheader', true);

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

        $this->add_action_buttons(false, get_string('createcourse_easyadmin', 'local_easyadmin'));
    }

    /**
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);

        $shortname = trim($data['shortname']);
        if ($shortname !== '' && $DB->record_exists('course', ['shortname' => $shortname])) {
            $errors['shortname'] = get_string('error_shortnametaken_easyadmin', 'local_easyadmin');
        }

        $teacheridentifier = trim($data['teacheridentifier']);
        if ($teacheridentifier !== '' && !local_easyadmin_find_user_by_identifier($teacheridentifier)) {
            $errors['teacheridentifier'] = get_string('error_teachernotfound_easyadmin', 'local_easyadmin');
        }

        return $errors;
    }
}
