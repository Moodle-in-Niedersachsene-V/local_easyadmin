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
     * Baut das Formular zum Anlegen einer Kurskategorie auf.
     *
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

        // Optionale Rollenzuweisung in dieser neuen Kategorie.
        // Beim Anlegen einer Kategorie kann direkt eine Person eine Rolle im
        // Kontext genau dieser Kategorie erhalten (z. B. eine Lehrkraft als
        // Kursersteller/in der neuen Fachschaft). Beide Felder sind optional:
        // Nur wenn eine Person gewählt wird, erfolgt eine Zuweisung.
        $mform->addElement(
            'header',
            'roleassignheader',
            get_string('categoryrole_header_easyadmin', 'local_easyadmin')
        );
        $mform->setExpanded('roleassignheader', true);

        // Durchsuchbares Auswahlfeld mit den vorhandenen Nutzer/innen. Die
        // Optionen werden vorab geladen (kein AJAX-Handler nötig); das Element
        // bietet Tippen-zum-Filtern und fällt sonst auf ein normales
        // Auswahlfeld zurück.
        $useroptions = self::get_user_options();
        $mform->addElement(
            'autocomplete',
            'assignuserid',
            get_string('categoryrole_user_easyadmin', 'local_easyadmin'),
            $useroptions,
            ['noselectionstring' => get_string('categoryrole_user_none_easyadmin', 'local_easyadmin')]
        );
        $mform->setType('assignuserid', PARAM_INT);
        $mform->addHelpButton('assignuserid', 'categoryrole_user_easyadmin', 'local_easyadmin');

        // Rollenauswahl. Wert = Moodle-Rollen-Shortname; die Auflösung zur
        // Rollen-ID passiert erst im Speicher-Handler (Shortnames sind stabil,
        // IDs können pro Installation abweichen).
        $roleoptions = [
            'coursecreator' => get_string('categoryrole_role_coursecreator_easyadmin', 'local_easyadmin'),
            'manager'       => get_string('categoryrole_role_manager_easyadmin', 'local_easyadmin'),
        ];
        $mform->addElement(
            'select',
            'assignrole',
            get_string('categoryrole_role_easyadmin', 'local_easyadmin'),
            $roleoptions
        );
        $mform->setType('assignrole', PARAM_ALPHA);
        $mform->setDefault('assignrole', 'coursecreator');
        $mform->addHelpButton('assignrole', 'categoryrole_role_easyadmin', 'local_easyadmin');
        // Die Rollenauswahl ist nur relevant, wenn auch eine Person gewählt ist.
        $mform->hideIf('assignrole', 'assignuserid', 'eq', 0);

        $this->add_action_buttons(false, get_string('createcategory_easyadmin', 'local_easyadmin'));
    }

    /**
     * Baut die Optionsliste für das Nutzer-Auswahlfeld.
     *
     * Bewusst ohne Gast/gelöschte/gesperrte Konten. Für die Größenordnung einer
     * Grundschule ist eine vorab geladene Liste ausreichend und robuster als
     * ein AJAX-Nutzerselektor.
     *
     * @return array [userid => "Name (E-Mail)"], plus 0 => "— keine —"
     */
    public static function get_user_options(): array {
        global $DB, $CFG;

        $options = [0 => get_string('categoryrole_user_none_easyadmin', 'local_easyadmin')];

        $guestid = isset($CFG->siteguest) ? (int) $CFG->siteguest : 0;
        $select = 'deleted = 0 AND suspended = 0' . ($guestid ? ' AND id <> :guestid' : '');
        $params = $guestid ? ['guestid' => $guestid] : [];

        // Die Funktion fullname() benötigt ALLE Namensfelder (firstname,
        // lastname sowie firstnamephonetic, lastnamephonetic, middlename,
        // alternatename). \core_user\fields::get_name_fields() liefert genau
        // diese Liste (der dokumentierte Ersatz für das veraltete
        // get_all_user_name_fields()). So wird kein Feld vergessen und
        // fullname() erzeugt keine Debug-Warnung mehr.
        $namefields = \core_user\fields::get_name_fields();
        $fields = 'id, email, ' . implode(', ', $namefields);

        $users = $DB->get_records_select(
            'user',
            $select,
            $params,
            'lastname ASC, firstname ASC',
            $fields
        );

        foreach ($users as $user) {
            $options[$user->id] = fullname($user) . ' (' . $user->email . ')';
        }

        return $options;
    }
}
