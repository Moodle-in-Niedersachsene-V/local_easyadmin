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
 * Formular für die vereinfachten Startseiten-Einstellungen.
 *
 * Bildet die wichtigsten Felder von Website-Administration > Allgemein >
 * Startseite ab: Name der Website, Kurzbezeichnung, Informationen zur
 * Website sowie die Anzeige vor/nach der Anmeldung.
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class frontpage_form extends \moodleform {
    /**
     * Baut das Formular für die Startseiten-Einstellungen auf.
     *
     * @return void
     */
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('text', 'fullname', get_string('fullsitename'), ['maxlength' => 100]);
        $mform->setType('fullname', PARAM_TEXT);
        $mform->addRule('fullname', get_string('error_required', 'local_easyadmin'), 'required', null, 'client');

        $mform->addElement('text', 'shortname', get_string('shortsitename'), ['maxlength' => 100]);
        $mform->setType('shortname', PARAM_TEXT);
        $mform->addRule('shortname', get_string('error_required', 'local_easyadmin'), 'required', null, 'client');

        $mform->addElement(
            'textarea',
            'summary',
            get_string('frontpagesummary_easyadmin', 'local_easyadmin'),
            ['rows' => 5, 'cols' => 60]
        );
        $mform->setType('summary', PARAM_TEXT);
        $mform->addHelpButton('summary', 'frontpagesummary_easyadmin', 'local_easyadmin');

        $mform->addElement(
            'static',
            'summary_hint',
            '',
            get_string('summary_blockhint', 'local_easyadmin')
        );

        $mform->addElement(
            'advcheckbox',
            'summary_showblock',
            get_string('summary_showblock', 'local_easyadmin')
        );
        $mform->addHelpButton('summary_showblock', 'summary_showblock', 'local_easyadmin');

        $displayoptions = $this->get_frontpage_display_options();

        $mform->addElement(
            'select',
            'frontpage',
            get_string('frontpage_easyadmin', 'local_easyadmin'),
            $displayoptions
        );
        $mform->addHelpButton('frontpage', 'frontpage_easyadmin', 'local_easyadmin');

        $mform->addElement(
            'select',
            'frontpageloggedin',
            get_string('frontpageloggedin_easyadmin', 'local_easyadmin'),
            $displayoptions
        );
        $mform->addHelpButton('frontpageloggedin', 'frontpageloggedin_easyadmin', 'local_easyadmin');

        $this->add_action_buttons(true, get_string('savechanges'));
    }

    /**
     * Liefert die von Moodle-Core vorgesehenen Anzeigeoptionen für die
     * Startseite, unter Verwendung der Core-Konstanten/Sprachstrings (keine
     * eigenen, möglicherweise falschen Zahlenwerte).
     *
     * @return array
     */
    protected function get_frontpage_display_options(): array {
        // Bewusst eigene, garantiert vorhandene Sprachtexte statt Core-Strings
        // zu erraten (die sich zwischen Moodle-Versionen ändern können). Die
        // Prüfung mit defined() schützt zusätzlich davor, dass eine in einer
        // bestimmten Moodle-Version nicht (mehr) existierende Konstante das
        // Formular zum Absturz bringt.
        $map = [
            'FRONTPAGENEWS' => get_string('frontpageoption_news', 'local_easyadmin'),
            'FRONTPAGECATEGORYNAMES' => get_string('frontpageoption_categories', 'local_easyadmin'),
            'FRONTPAGECATEGORYCOMBO' => get_string('frontpageoption_combo', 'local_easyadmin'),
            'FRONTPAGEALLCOURSELIST' => get_string('frontpageoption_allcourses', 'local_easyadmin'),
            'FRONTPAGEENROLLEDCOURSELIST' => get_string('frontpageoption_enrolledcourses', 'local_easyadmin'),
            'FRONTPAGECOURSESEARCH' => get_string('frontpageoption_coursesearch', 'local_easyadmin'),
        ];

        // Die Option "Keine Anzeige" steht zuerst in der Liste, analog zur
        // Moodle-Core-Option.
        $options = ['' => get_string('frontpageoption_none', 'local_easyadmin')];
        foreach ($map as $constant => $label) {
            if (defined($constant)) {
                $options[constant($constant)] = $label;
            }
        }

        return $options;
    }
}
