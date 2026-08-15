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
 * Formular zum Anlegen/Bearbeiten einer einzelnen Schul-Kachel für die
 * Startseite.
 *
 * Ersetzt die frühere Verwaltung über 64 einzelne Website-Administrations-
 * Rohfelder (lokal_schooltiles) durch ein reguläres moodleform mit
 * automatischem Sesskey-Schutz. Insbesondere die Kategorie-Auswahl ist jetzt
 * ein echtes Dropdown mit Namen statt einer blind einzutippenden Kategorie-ID.
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class schooltile_form extends \moodleform {
    /** Erlaubtes Farbformat, z. B. #1A2B3C. */
    const COLOR_PATTERN = '/^#[0-9A-Fa-f]{6}$/';

    /**
     * Baut das Formular zum Anlegen/Bearbeiten einer Schul-Kachel auf.
     *
     * @return void
     */
    protected function definition() {
        global $DB;

        $mform = $this->_form;

        $mform->addElement('text', 'name', get_string('schooltile_name', 'local_easyadmin'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('error_required', 'local_easyadmin'), 'required', null, 'client');
        $mform->addHelpButton('name', 'schooltile_name', 'local_easyadmin');

        $mform->addElement('text', 'icon', get_string('schooltile_icon', 'local_easyadmin'), ['size' => 4]);
        $mform->setType('icon', PARAM_TEXT);
        $mform->setDefault('icon', '📚');
        $mform->addHelpButton('icon', 'schooltile_icon', 'local_easyadmin');

        $mform->addElement(
            'filemanager',
            'image',
            get_string('schooltile_image', 'local_easyadmin'),
            null,
            [
                'subdirs' => 0,
                'maxfiles' => 1,
                'maxbytes' => 1 * 1024 * 1024,
                'accepted_types' => ['web_image'],
            ]
        );
        $mform->addHelpButton('image', 'schooltile_image', 'local_easyadmin');

        $colorfield = $mform->addElement('text', 'color', get_string('schooltile_color', 'local_easyadmin'));
        $mform->setType('color', PARAM_TEXT);
        $mform->setDefault('color', '#4A90E2');
        $colorfield->updateAttributes(['type' => 'color']);
        $mform->addHelpButton('color', 'schooltile_color', 'local_easyadmin');

        $textcolorfield = $mform->addElement('text', 'textcolor', get_string('schooltile_textcolor', 'local_easyadmin'));
        $mform->setType('textcolor', PARAM_TEXT);
        $mform->setDefault('textcolor', '#FFFFFF');
        $textcolorfield->updateAttributes(['type' => 'color']);
        $mform->addHelpButton('textcolor', 'schooltile_textcolor', 'local_easyadmin');

        $mform->addElement('header', 'targetheader', get_string('schooltile_targetheader', 'local_easyadmin'));
        $mform->setExpanded('targetheader', true);

        $targettypes = [
            'category' => get_string('schooltile_targettype_category', 'local_easyadmin'),
            'url'      => get_string('schooltile_targettype_url', 'local_easyadmin'),
        ];
        $mform->addElement(
            'select',
            'targettype',
            get_string('schooltile_targettype', 'local_easyadmin'),
            $targettypes
        );
        $mform->addHelpButton('targettype', 'schooltile_targettype', 'local_easyadmin');

        $categoryoptions = \core_course_category::make_categories_list();
        $mform->addElement(
            'select',
            'categoryid',
            get_string('schooltile_categoryid', 'local_easyadmin'),
            $categoryoptions
        );
        $mform->hideIf('categoryid', 'targettype', 'neq', 'category');

        $mform->addElement('text', 'url', get_string('schooltile_url', 'local_easyadmin'));
        $mform->setType('url', PARAM_TEXT);
        $mform->hideIf('url', 'targettype', 'neq', 'url');
        $mform->addHelpButton('url', 'schooltile_url', 'local_easyadmin');

        $cohortoptions = [0 => get_string('schooltile_cohort_all', 'local_easyadmin')];
        $cohorts = $DB->get_records('cohort', null, 'name ASC', 'id, name, idnumber');
        foreach ($cohorts as $cohort) {
            $label = format_string($cohort->name);
            if (!empty($cohort->idnumber)) {
                $label .= ' (' . $cohort->idnumber . ')';
            }
            $cohortoptions[$cohort->id] = $label;
        }
        $mform->addElement('select', 'cohortid', get_string('schooltile_cohort', 'local_easyadmin'), $cohortoptions);
        $mform->addHelpButton('cohortid', 'schooltile_cohort', 'local_easyadmin');

        $mform->addElement('hidden', 'tilenumber');
        $mform->setType('tilenumber', PARAM_INT);

        $this->add_action_buttons(true, get_string('schooltile_save', 'local_easyadmin'));
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

        if (!empty($data['color']) && !preg_match(self::COLOR_PATTERN, $data['color'])) {
            $errors['color'] = get_string('error_invalidcolor', 'local_easyadmin');
        }

        if (!empty($data['textcolor']) && !preg_match(self::COLOR_PATTERN, $data['textcolor'])) {
            $errors['textcolor'] = get_string('error_invalidcolor', 'local_easyadmin');
        }

        if ($data['targettype'] === 'url' && trim((string) $data['url']) !== '') {
            try {
                new \moodle_url($data['url']);
            } catch (\moodle_exception $e) {
                $errors['url'] = get_string('error_invalidurl', 'local_easyadmin');
            }
        }

        return $errors;
    }
}
