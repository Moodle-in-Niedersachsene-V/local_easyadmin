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
 * Formular zum gleichzeitigen Anlegen mehrerer Unterkategorien aus einer
 * Liste - z. B. eine Unterkategorie je Lehrkraft-Kürzel unter der
 * übergeordneten Kategorie "Lehrkräfte".
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class bulk_category_create_form extends \moodleform {

    /**
     * @return void
     */
    protected function definition() {
        $mform = $this->_form;

        $categoryoptions = \core_course_category::make_categories_list('moodle/category:manage');
        $categoryoptions = [0 => get_string('categoryparent_top_easyadmin', 'local_easyadmin')] + $categoryoptions;

        $mform->addElement(
            'select',
            'parent',
            get_string('bulkcategory_parent_easyadmin', 'local_easyadmin'),
            $categoryoptions
        );
        $mform->setDefault('parent', 0);
        $mform->addHelpButton('parent', 'bulkcategory_parent_easyadmin', 'local_easyadmin');

        $mform->addElement(
            'textarea',
            'names',
            get_string('bulkcategory_names_easyadmin', 'local_easyadmin'),
            ['rows' => 8, 'cols' => 40]
        );
        $mform->setType('names', PARAM_RAW);
        $mform->addRule('names', get_string('error_required', 'local_easyadmin'), 'required', null, 'client');
        $mform->addHelpButton('names', 'bulkcategory_names_easyadmin', 'local_easyadmin');

        $this->add_action_buttons(false, get_string('bulkcategory_create_button', 'local_easyadmin'));
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
            // Grobe Obergrenze, um ein versehentliches Einfügen einer riesigen
            // Textmenge (z. B. eines ganzen Dokuments) abzufangen.
            $errors['names'] = get_string('error_bulkcategory_toomany', 'local_easyadmin');
        }

        return $errors;
    }
}
