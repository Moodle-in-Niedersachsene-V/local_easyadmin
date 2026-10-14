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

namespace local_easyadmin\task;

/**
 * Löscht automatisch Nutzerkonten, die seit einer einstellbaren Anzahl Tage
 * inaktiv sind - nur, wenn diese Funktion über die Kachel "Nutzer & Gruppen"
 * > "Nutzerkonten löschen" ausdrücklich aktiviert wurde (Standard: deaktiviert).
 *
 * Website-Administrator/innen und das Gastkonto sind von dieser Löschung
 * grundsätzlich ausgeschlossen (siehe local_easyadmin_get_inactive_users()).
 * Die eigentliche Löschung erfolgt über Moodles offizielle Funktion
 * user_delete_user(), die Konten als gelöscht markiert und anonymisiert,
 * nicht physisch aus der Datenbank entfernt (Standard-Moodle-Verhalten, u. a.
 * zur Wahrung der referenziellen Integrität bei Noten/Protokolldaten).
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cleanup_inactive_users extends \core\task\scheduled_task {
    /**
     * Liefert den in der Aufgabenverwaltung angezeigten Namen dieser Aufgabe.
     *
     * @return string
     */
    public function get_name() {
        return get_string('task_cleanup_inactive_users', 'local_easyadmin');
    }

    /**
     * Führt die Löschung inaktiver Nutzerkonten aus, sofern aktiviert.
     *
     * @return void
     */
    public function execute() {
        global $DB, $CFG;

        if (empty(get_config('local_easyadmin', 'inactivedeletion_enabled'))) {
            mtrace('local_easyadmin: Automatische Löschung inaktiver Konten ist deaktiviert, keine Aktion.');
            return;
        }

        $days = (int) get_config('local_easyadmin', 'inactivedeletion_days');
        if ($days < LOCAL_EASYADMIN_INACTIVEDELETION_MINDAYS) {
            $days = LOCAL_EASYADMIN_INACTIVEDELETION_MINDAYS;
        }

        require_once($CFG->dirroot . '/user/lib.php');
        require_once($CFG->dirroot . '/local/easyadmin/lib.php');

        $candidates = local_easyadmin_get_inactive_users($days);
        $deletedcount = 0;

        foreach ($candidates as $candidate) {
            $user = $DB->get_record('user', ['id' => $candidate->id, 'deleted' => 0]);
            if (!$user || is_siteadmin($user)) {
                // Verteidigung in der Tiefe: doppelte Prüfung unmittelbar vor
                // der eigentlichen Löschung, auch wenn local_easyadmin_get_inactive_users()
                // Administrator/innen bereits ausschließt.
                continue;
            }

            user_delete_user($user);
            $deletedcount++;
        }

        mtrace('local_easyadmin: ' . $deletedcount . ' inaktive(s) Nutzerkonto/Nutzerkonten gelöscht (Schwelle: '
            . $days . ' Tage ohne Aktivität).');
    }
}
