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
 * Formular für die vereinfachten Sicherheits- und Datenschutz-Einstellungen.
 *
 * Bildet eine bewusst kleine, für Grundschul-Admins relevante Auswahl aus
 * Website-Administration > Allgemein > Sicherheit sowie
 * datenschutzbezogenen Kerneinstellungen ab.
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class security_form extends \moodleform {
    /**
     * Baut das Formular für die Sicherheitseinstellungen auf.
     *
     * @return void
     */
    protected function definition() {
        $mform = $this->_form;

        // Sicherheit.
        $mform->addElement('header', 'securityheader', get_string('securityheader', 'local_easyadmin'));
        $mform->setExpanded('securityheader', true);

        $mform->addElement('advcheckbox', 'forcelogin', get_string('forcelogin_easyadmin', 'local_easyadmin'));
        $mform->addHelpButton('forcelogin', 'forcelogin_easyadmin', 'local_easyadmin');

        $mform->addElement('advcheckbox', 'guestloginbutton', get_string('guestloginbutton_easyadmin', 'local_easyadmin'));
        $mform->addHelpButton('guestloginbutton', 'guestloginbutton_easyadmin', 'local_easyadmin');

        $mform->addElement('advcheckbox', 'passwordpolicy', get_string('passwordpolicy_easyadmin', 'local_easyadmin'));
        $mform->addHelpButton('passwordpolicy', 'passwordpolicy_easyadmin', 'local_easyadmin');

        $mform->addElement(
            'text',
            'minpasswordlength',
            get_string('minpasswordlength_easyadmin', 'local_easyadmin'),
            ['size' => 4]
        );
        $mform->setType('minpasswordlength', PARAM_INT);
        $mform->hideIf('minpasswordlength', 'passwordpolicy', 'notchecked');

        $mform->addElement(
            'text',
            'minpassworddigits',
            get_string('minpassworddigits_easyadmin', 'local_easyadmin'),
            ['size' => 4]
        );
        $mform->setType('minpassworddigits', PARAM_INT);
        $mform->addHelpButton('minpassworddigits', 'minpassworddigits_easyadmin', 'local_easyadmin');
        $mform->hideIf('minpassworddigits', 'passwordpolicy', 'notchecked');

        $mform->addElement(
            'text',
            'minpasswordlower',
            get_string('minpasswordlower_easyadmin', 'local_easyadmin'),
            ['size' => 4]
        );
        $mform->setType('minpasswordlower', PARAM_INT);
        $mform->addHelpButton('minpasswordlower', 'minpasswordlower_easyadmin', 'local_easyadmin');
        $mform->hideIf('minpasswordlower', 'passwordpolicy', 'notchecked');

        $mform->addElement(
            'text',
            'minpasswordupper',
            get_string('minpasswordupper_easyadmin', 'local_easyadmin'),
            ['size' => 4]
        );
        $mform->setType('minpasswordupper', PARAM_INT);
        $mform->addHelpButton('minpasswordupper', 'minpasswordupper_easyadmin', 'local_easyadmin');
        $mform->hideIf('minpasswordupper', 'passwordpolicy', 'notchecked');

        $mform->addElement(
            'text',
            'minpasswordnonalphanum',
            get_string('minpasswordnonalphanum_easyadmin', 'local_easyadmin'),
            ['size' => 4]
        );
        $mform->setType('minpasswordnonalphanum', PARAM_INT);
        $mform->addHelpButton('minpasswordnonalphanum', 'minpasswordnonalphanum_easyadmin', 'local_easyadmin');
        $mform->hideIf('minpasswordnonalphanum', 'passwordpolicy', 'notchecked');

        $mform->addElement(
            'text',
            'maxconsecutiveidentchars',
            get_string('maxconsecutiveidentchars_easyadmin', 'local_easyadmin'),
            ['size' => 4]
        );
        $mform->setType('maxconsecutiveidentchars', PARAM_INT);
        $mform->addHelpButton('maxconsecutiveidentchars', 'maxconsecutiveidentchars_easyadmin', 'local_easyadmin');
        $mform->hideIf('maxconsecutiveidentchars', 'passwordpolicy', 'notchecked');

        $mform->addElement(
            'text',
            'lockoutthreshold',
            get_string('lockoutthreshold_easyadmin', 'local_easyadmin'),
            ['size' => 4]
        );
        $mform->setType('lockoutthreshold', PARAM_INT);
        $mform->addHelpButton('lockoutthreshold', 'lockoutthreshold_easyadmin', 'local_easyadmin');

        $mform->addElement(
            'advcheckbox',
            'displayloginfailures',
            get_string('displayloginfailures_easyadmin', 'local_easyadmin')
        );
        $mform->addHelpButton('displayloginfailures', 'displayloginfailures_easyadmin', 'local_easyadmin');

        $mform->addElement(
            'advcheckbox',
            'allowframembedding',
            get_string('allowframembedding_easyadmin', 'local_easyadmin')
        );
        $mform->addHelpButton('allowframembedding', 'allowframembedding_easyadmin', 'local_easyadmin');

        // Datenschutz.
        $mform->addElement('header', 'privacyheader', get_string('privacyheader', 'local_easyadmin'));
        $mform->setExpanded('privacyheader', true);

        $mform->addElement(
            'advcheckbox',
            'sitepolicyhandler_usetool',
            get_string('sitepolicyhandler_usetool', 'local_easyadmin')
        );
        $mform->addHelpButton('sitepolicyhandler_usetool', 'sitepolicyhandler_usetool', 'local_easyadmin');

        $policyurl = new \moodle_url('/admin/tool/policy/managedocs.php');
        $policylink = \html_writer::link(
            $policyurl,
            get_string('policytool_linktext', 'local_easyadmin'),
            ['class' => 'btn btn-secondary', 'target' => '_blank']
        );
        $mform->addElement(
            'static',
            'policytool_hint',
            get_string('policytool_heading', 'local_easyadmin'),
            get_string('policytool_hint', 'local_easyadmin') . '<br><br>' . $policylink
        );

        $mform->addElement('text', 'sitepolicy', get_string('sitepolicy_easyadmin', 'local_easyadmin'));
        $mform->setType('sitepolicy', PARAM_TEXT);
        $mform->addHelpButton('sitepolicy', 'sitepolicy_easyadmin', 'local_easyadmin');

        $this->add_action_buttons(true, get_string('savechanges'));
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

        if (!empty($data['passwordpolicy'])) {
            $minlength = (int) $data['minpasswordlength'];
            if ($minlength < 4 || $minlength > 64) {
                $errors['minpasswordlength'] = get_string('error_minpasswordlength_range', 'local_easyadmin');
            }

            $partfields = ['minpassworddigits', 'minpasswordlower', 'minpasswordupper', 'minpasswordnonalphanum'];
            foreach ($partfields as $field) {
                $value = (int) $data[$field];
                if ($value < 0 || $value > 20) {
                    $errors[$field] = get_string('error_passwordpart_range', 'local_easyadmin');
                }
            }

            $consecutive = (int) $data['maxconsecutiveidentchars'];
            if ($consecutive < 0 || $consecutive > 20) {
                $errors['maxconsecutiveidentchars'] = get_string('error_passwordpart_range', 'local_easyadmin');
            }
        }

        $threshold = (int) $data['lockoutthreshold'];
        if ($threshold < 1 || $threshold > 100) {
            $errors['lockoutthreshold'] = get_string('error_lockoutthreshold_range', 'local_easyadmin');
        }

        if (trim((string) $data['sitepolicy']) !== '') {
            try {
                new \moodle_url($data['sitepolicy']);
            } catch (\moodle_exception $e) {
                $errors['sitepolicy'] = get_string('error_invalidurl', 'local_easyadmin');
            }
        }

        return $errors;
    }
}
