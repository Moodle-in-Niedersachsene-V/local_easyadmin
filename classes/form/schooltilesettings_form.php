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
 * Formular für Größe und Ausrichtung der Schul-Kacheln auf der Startseite.
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class schooltilesettings_form extends \moodleform {
    /**
     * Baut das Formular für Kachelgröße und Ausrichtung auf.
     *
     * @return void
     */
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'settingsheader', get_string('schooltiles_settingsheader', 'local_easyadmin'));

        $sizeoptions = [
            'small' => get_string('schooltiles_size_small', 'local_easyadmin'),
            'medium' => get_string('schooltiles_size_medium', 'local_easyadmin'),
            'large' => get_string('schooltiles_size_large', 'local_easyadmin'),
        ];
        $mform->addElement('select', 'tilesize', get_string('schooltiles_size', 'local_easyadmin'), $sizeoptions);
        $mform->setType('tilesize', PARAM_ALPHA);
        $mform->addHelpButton('tilesize', 'schooltiles_size', 'local_easyadmin');

        $alignoptions = [
            'left' => get_string('schooltiles_align_left', 'local_easyadmin'),
            'center' => get_string('schooltiles_align_center', 'local_easyadmin'),
            'right' => get_string('schooltiles_align_right', 'local_easyadmin'),
        ];
        $mform->addElement('select', 'tilealign', get_string('schooltiles_align', 'local_easyadmin'), $alignoptions);
        $mform->setType('tilealign', PARAM_ALPHA);
        $mform->addHelpButton('tilealign', 'schooltiles_align', 'local_easyadmin');

        $this->add_action_buttons(false, get_string('savechanges'));
    }
}
