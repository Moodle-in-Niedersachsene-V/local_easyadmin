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
 * Formular zum gleichzeitigen Anlegen mehrerer globaler Gruppen (Kohorten)
 * aus einer Liste.
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class bulk_cohort_create_form extends \moodleform {

    /**
     * @return void
     */
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement(
            'textarea',
            'names',
            get_string('bulkcohort_names_easyadmin', 'local_easyadmin'),
            ['rows' => 8, 'cols' => 40]
        );
        $mform->setType('names', PARAM_RAW);
        $mform->addRule('names', get_string('error_required', 'local_easyadmin'), 'required', null, 'client');
        $mform->addHelpButton('names', 'bulkcohort_names_easyadmin', 'local_easyadmin');

        $this->add_action_buttons(false, get_string('bulkcohort_create_button', 'local_easyadmin'));
    }

    /**
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $lines = array_filter(array_map('trim', explode("\n", str_replace("\r", '', $data['names']))));
        if (empty($lines)) {
            $errors['names'] = get_string('error_required', 'local_easyadmin');
        } else if (count($lines) > 200) {
            $errors['names'] = get_string('error_bulkcategory_toomany', 'local_easyadmin');
        }

        return $errors;
    }
}
