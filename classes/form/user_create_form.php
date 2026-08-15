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
 * Formular zum Anlegen eines manuellen Nutzerkontos.
 *
 * Erstellt ausschließlich Konten mit Authentifizierungsmethode 'manual'
 * (kein SSO/LDAP/OAuth) - passend zur Anforderung, dass insbesondere
 * Administrator-Konten immer manuelle Konten sein sollen.
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_create_form extends \moodleform {
    /**
     * Baut das Formular zum Anlegen eines Nutzerkontos auf.
     *
     * @return void
     */
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement(
            'static',
            'manualaccount_notice',
            '',
            \html_writer::div(get_string('manualaccount_notice', 'local_easyadmin'), 'alert alert-info')
        );

        $mform->addElement('text', 'firstname', get_string('firstname'));
        $mform->setType('firstname', PARAM_TEXT);
        $mform->addRule('firstname', get_string('error_required', 'local_easyadmin'), 'required', null, 'client');

        $mform->addElement('text', 'lastname', get_string('lastname'));
        $mform->setType('lastname', PARAM_TEXT);
        $mform->addRule('lastname', get_string('error_required', 'local_easyadmin'), 'required', null, 'client');

        $mform->addElement('text', 'email', get_string('email'));
        $mform->setType('email', PARAM_EMAIL);
        $mform->addRule('email', get_string('error_required', 'local_easyadmin'), 'required', null, 'client');

        $mform->addElement('text', 'username', get_string('username'));
        $mform->setType('username', PARAM_RAW);
        $mform->addRule('username', get_string('error_required', 'local_easyadmin'), 'required', null, 'client');
        $mform->addHelpButton('username', 'username_easyadmin', 'local_easyadmin');

        $mform->addElement('password', 'password', get_string('password'), ['autocomplete' => 'new-password']);
        $mform->setType('password', PARAM_RAW);
        $mform->addRule('password', get_string('error_required', 'local_easyadmin'), 'required', null, 'client');
        $mform->addHelpButton('password', 'password_easyadmin', 'local_easyadmin');

        $mform->addElement(
            'advcheckbox',
            'forcepasswordchange',
            get_string('forcepasswordchange_easyadmin', 'local_easyadmin')
        );
        $mform->setDefault('forcepasswordchange', 1);

        // Zum Administrator machen: nur anzeigen, wenn die aktuell
        // ausfüllende Person selbst bereits vollen Admin-Zugriff
        // (moodle/site:config) besitzt - siehe users.php.
        if (!empty($this->_customdata['showmakeadmin'])) {
            $mform->addElement(
                'static',
                'makeadmin_warning',
                '',
                \html_writer::div(get_string('makeadmin_warning', 'local_easyadmin'), 'alert alert-danger')
            );
            $mform->addElement('advcheckbox', 'makeadmin', get_string('makeadmin_easyadmin', 'local_easyadmin'));
            $mform->addHelpButton('makeadmin', 'makeadmin_easyadmin', 'local_easyadmin');
        }

        $this->add_action_buttons(true, get_string('createuser_easyadmin', 'local_easyadmin'));
    }

    /**
     * Validiert die eingegebenen Formulardaten serverseitig.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        global $DB, $CFG;

        $errors = parent::validation($data, $files);

        $username = trim(\core_text::strtolower($data['username']));
        if ($username === '') {
            $errors['username'] = get_string('error_required', 'local_easyadmin');
        } else if (!preg_match('/^[a-z0-9.\-_@]+$/', $username)) {
            $errors['username'] = get_string('error_invalidusername', 'local_easyadmin');
        } else if ($DB->record_exists('user', ['username' => $username, 'mnethostid' => $CFG->mnet_localhost_id])) {
            $errors['username'] = get_string('error_usernametaken', 'local_easyadmin');
        }

        if (!validate_email($data['email'])) {
            $errors['email'] = get_string('error_invalidemail', 'local_easyadmin');
        } else if (
            empty($CFG->allowaccountssameemail)
            && $DB->record_exists('user', [
                'email' => $data['email'],
                'mnethostid' => $CFG->mnet_localhost_id,
                'deleted' => 0,
            ])
        ) {
            $errors['email'] = get_string('error_emailtaken', 'local_easyadmin');
        }

        $passworderrors = '';
        if (!check_password_policy($data['password'], $passworderrors)) {
            $errors['password'] = $passworderrors;
        }

        return $errors;
    }
}
