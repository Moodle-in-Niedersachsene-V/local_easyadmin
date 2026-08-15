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
 * Formular für den Design-Bereich des easyadmin-Dashboards.
 *
 * moodleform bringt automatisch Sesskey-/CSRF-Schutz mit - kein manueller
 * require_sesskey() nötig.
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class design_form extends \moodleform {
    /** Erlaubtes Farbformat, z. B. #1A2B3C. */
    const COLOR_PATTERN = '/^#[0-9A-Fa-f]{6}$/';

    /**
     * Baut das Formular für die Design-Einstellungen auf.
     *
     * @return void
     */
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement(
            'filemanager',
            'logo',
            get_string('field_logo', 'local_easyadmin'),
            null,
            [
                'subdirs' => 0,
                'maxfiles' => 1,
                'maxbytes' => 1 * 1024 * 1024,
                'accepted_types' => ['web_image'],
            ]
        );
        $mform->addHelpButton('logo', 'field_logo', 'local_easyadmin');

        $mform->addElement(
            'filemanager',
            'backgroundimage',
            get_string('backgroundimage', 'local_easyadmin'),
            null,
            [
                'subdirs' => 0,
                'maxfiles' => 1,
                'maxbytes' => 3 * 1024 * 1024,
                'accepted_types' => ['web_image'],
            ]
        );
        $mform->addHelpButton('backgroundimage', 'backgroundimage', 'local_easyadmin');

        $colorfield = $mform->addElement(
            'text',
            'backgroundcolor',
            get_string('backgroundcolor', 'local_easyadmin')
        );
        $mform->setType('backgroundcolor', PARAM_TEXT);
        $mform->addHelpButton('backgroundcolor', 'backgroundcolor', 'local_easyadmin');
        $colorfield->updateAttributes(['type' => 'color']);

        $textcolorfield = $mform->addElement(
            'text',
            'textcolor',
            get_string('textcolor', 'local_easyadmin')
        );
        $mform->setType('textcolor', PARAM_TEXT);
        $textcolorfield->updateAttributes(['type' => 'color']);

        $sizes = [
            'small'  => get_string('textsize_small', 'local_easyadmin'),
            'normal' => get_string('textsize_normal', 'local_easyadmin'),
            'large'  => get_string('textsize_large', 'local_easyadmin'),
            'xlarge' => get_string('textsize_xlarge', 'local_easyadmin'),
        ];
        $mform->addElement('select', 'textsize', get_string('textsize', 'local_easyadmin'), $sizes);
        $mform->setType('textsize', PARAM_ALPHA);
        $mform->setDefault('textsize', 'normal');

        // Maximale Seiten-/Inhaltsbreite. Leerer Wert ('') = Moodle-Standard,
        // dann wird keine überschreibende Regel injiziert. Die übrigen Werte
        // sind gültige CSS-Breitenangaben, die direkt in die max-width-Regel
        // übernommen werden (siehe hook_callbacks::before_standard_head_...).
        $pagewidths = [
            ''       => get_string('pagewidth_default', 'local_easyadmin'),
            '1200px' => get_string('pagewidth_medium', 'local_easyadmin'),
            '1400px' => get_string('pagewidth_wide', 'local_easyadmin'),
            '100%'   => get_string('pagewidth_full', 'local_easyadmin'),
        ];
        $mform->addElement('select', 'pagewidth', get_string('pagewidth', 'local_easyadmin'), $pagewidths);
        $mform->setType('pagewidth', PARAM_TEXT);
        $mform->setDefault('pagewidth', '');
        $mform->addHelpButton('pagewidth', 'pagewidth', 'local_easyadmin');

        // Anmeldeseite (überschreibt das von Moodle seit 5.2 mitgelieferte
        // Standard-KI-Bild auf der Login-Seite).
        $mform->addElement('header', 'loginpageheader', get_string('loginpageheader', 'local_easyadmin'));
        $mform->setExpanded('loginpageheader', true);

        $mform->addElement(
            'advcheckbox',
            'loginpage_hidedefaultimage',
            get_string('loginpage_hidedefaultimage', 'local_easyadmin')
        );
        $mform->addHelpButton('loginpage_hidedefaultimage', 'loginpage_hidedefaultimage', 'local_easyadmin');

        $mform->addElement(
            'filemanager',
            'loginpage_image',
            get_string('loginpage_image', 'local_easyadmin'),
            null,
            [
                'subdirs' => 0,
                'maxfiles' => 1,
                'maxbytes' => 3 * 1024 * 1024,
                'accepted_types' => ['web_image'],
            ]
        );
        $mform->addHelpButton('loginpage_image', 'loginpage_image', 'local_easyadmin');
        $mform->hideIf('loginpage_image', 'loginpage_hidedefaultimage', 'notchecked');

        // Hinweistext neben der Anmeldemaske. Dieses Feld bearbeitet die
        // Moodle-Kerneinstellung auth_instructions (Website-Administration >
        // Anmelden > Anmeldeeinstellungen > "Anleitung"). Bewusst ohne
        // Datei-Uploads ('maxfiles' => 0), weil die Kerneinstellung nur einen
        // reinen HTML-String speichert und keinen eigenen Dateibereich besitzt.
        $mform->addElement(
            'editor',
            'loginpage_instructions',
            get_string('loginpage_instructions', 'local_easyadmin'),
            ['rows' => 8],
            [
                'subdirs' => 0,
                'maxfiles' => 0,
                'maxbytes' => 0,
                'trusttext' => false,
                'context' => \context_system::instance(),
                'enable_filemanagement' => false,
            ]
        );
        $mform->setType('loginpage_instructions', PARAM_RAW);
        $mform->addHelpButton('loginpage_instructions', 'loginpage_instructions', 'local_easyadmin');

        // Begrüßungs-Popup.
        $mform->addElement('header', 'welcomepopupheader', get_string('welcomepopupheader', 'local_easyadmin'));
        $mform->setExpanded('welcomepopupheader', true);

        $mform->addElement(
            'advcheckbox',
            'welcomepopup_enabled',
            get_string('welcomepopup_enabled', 'local_easyadmin')
        );
        $mform->addHelpButton('welcomepopup_enabled', 'welcomepopup_enabled', 'local_easyadmin');

        $mform->addElement(
            'filemanager',
            'welcomepopup_image',
            get_string('welcomepopup_image', 'local_easyadmin'),
            null,
            [
                'subdirs' => 0,
                'maxfiles' => 1,
                'maxbytes' => 1 * 1024 * 1024,
                'accepted_types' => ['web_image'],
            ]
        );
        $mform->addHelpButton('welcomepopup_image', 'welcomepopup_image', 'local_easyadmin');
        $mform->hideIf('welcomepopup_image', 'welcomepopup_enabled', 'notchecked');

        $mform->addElement(
            'text',
            'welcomepopup_title',
            get_string('welcomepopup_title', 'local_easyadmin'),
            ['maxlength' => 60]
        );
        $mform->setType('welcomepopup_title', PARAM_TEXT);
        $mform->hideIf('welcomepopup_title', 'welcomepopup_enabled', 'notchecked');

        $mform->addElement(
            'textarea',
            'welcomepopup_message',
            get_string('welcomepopup_message', 'local_easyadmin'),
            ['rows' => 3, 'cols' => 50]
        );
        $mform->setType('welcomepopup_message', PARAM_TEXT);
        $mform->hideIf('welcomepopup_message', 'welcomepopup_enabled', 'notchecked');

        $mform->addElement(
            'text',
            'welcomepopup_buttontext',
            get_string('welcomepopup_buttontext', 'local_easyadmin'),
            ['maxlength' => 40]
        );
        $mform->setType('welcomepopup_buttontext', PARAM_TEXT);
        $mform->hideIf('welcomepopup_buttontext', 'welcomepopup_enabled', 'notchecked');

        $mform->addElement(
            'text',
            'welcomepopup_buttonurl',
            get_string('welcomepopup_buttonurl', 'local_easyadmin')
        );
        $mform->setType('welcomepopup_buttonurl', PARAM_URL);
        $mform->addHelpButton('welcomepopup_buttonurl', 'welcomepopup_buttonurl', 'local_easyadmin');
        $mform->hideIf('welcomepopup_buttonurl', 'welcomepopup_enabled', 'notchecked');

        $mform->addElement(
            'text',
            'welcomepopup_autoclose',
            get_string('welcomepopup_autoclose', 'local_easyadmin'),
            ['size' => 4]
        );
        $mform->setType('welcomepopup_autoclose', PARAM_INT);
        $mform->addHelpButton('welcomepopup_autoclose', 'welcomepopup_autoclose', 'local_easyadmin');
        $mform->hideIf('welcomepopup_autoclose', 'welcomepopup_enabled', 'notchecked');

        $this->add_action_buttons(true, get_string('savechanges'));
    }

    /**
     * Serverseitige Validierung der Farbfelder (unabhängig vom
     * HTML5-Color-Picker, der theoretisch umgangen werden könnte).
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (!empty($data['backgroundcolor']) && !preg_match(self::COLOR_PATTERN, $data['backgroundcolor'])) {
            $errors['backgroundcolor'] = get_string('error_invalidcolor', 'local_easyadmin');
        }

        if (!empty($data['textcolor']) && !preg_match(self::COLOR_PATTERN, $data['textcolor'])) {
            $errors['textcolor'] = get_string('error_invalidcolor', 'local_easyadmin');
        }

        if (!empty($data['welcomepopup_enabled'])) {
            if (trim((string) $data['welcomepopup_title']) === '') {
                $errors['welcomepopup_title'] = get_string('error_required', 'local_easyadmin');
            }
            if (trim((string) $data['welcomepopup_buttonurl']) !== '') {
                try {
                    // Nur eine gültige, auf diese Website bezogene URL zulassen -
                    // verhindert Weiterleitung auf beliebige externe/fremde Ziele.
                    new \moodle_url($data['welcomepopup_buttonurl']);
                } catch (\moodle_exception $e) {
                    $errors['welcomepopup_buttonurl'] = get_string('error_invalidurl', 'local_easyadmin');
                }
            }

            if (isset($data['welcomepopup_autoclose'])) {
                $autoclose = (int) $data['welcomepopup_autoclose'];
                if ($autoclose < 0 || $autoclose > 120) {
                    $errors['welcomepopup_autoclose'] = get_string('error_autoclose_range', 'local_easyadmin');
                }
            }
        }

        return $errors;
    }
}
