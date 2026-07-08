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
 * Formular zum Anlegen einer Kurskategorie.
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class category_create_form extends \moodleform {

    /**
     * @return void
     */
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('text', 'name', get_string('categoryname_easyadmin', 'local_easyadmin'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('error_required', 'local_easyadmin'), 'required', null, 'client');

        $categoryoptions = \core_course_category::make_categories_list('moodle/category:manage');
        $categoryoptions = [0 => get_string('categoryparent_top_easyadmin', 'local_easyadmin')] + $categoryoptions;

        $mform->addElement(
            'select',
            'parent',
            get_string('categoryparent_easyadmin', 'local_easyadmin'),
            $categoryoptions
        );
        $mform->setDefault('parent', 0);

        $mform->addElement(
            'textarea',
            'description',
            get_string('categorydescription_easyadmin', 'local_easyadmin'),
            ['rows' => 3, 'cols' => 50]
        );
        $mform->setType('description', PARAM_TEXT);

        $this->add_action_buttons(false, get_string('createcategory_easyadmin', 'local_easyadmin'));
    }
}
