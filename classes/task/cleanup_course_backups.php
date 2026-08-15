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
 * Löscht automatisch Kurs-Sicherungen, die über die Kurse-Kachel erstellt
 * wurden und älter als die festgelegte Aufbewahrungsfrist sind.
 *
 * Diese Sicherungen können personenbezogene Daten (Teilnehmer/innen)
 * enthalten und sammeln sich sonst unbegrenzt im Dateibereich des Plugins
 * an - siehe Sicherheitsprüfung zur Kachel "Kurse".
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cleanup_course_backups extends \core\task\scheduled_task {
    /** Aufbewahrungsfrist in Tagen. */
    const RETENTION_DAYS = 20;

    /**
     * Liefert den in der Aufgabenverwaltung angezeigten Namen dieser Aufgabe.
     *
     * @return string
     */
    public function get_name() {
        return get_string('task_cleanup_course_backups', 'local_easyadmin');
    }

    /**
     * Führt die Löschung veralteter Kurs-Sicherungen aus.
     *
     * @return void
     */
    public function execute() {
        global $DB;

        $fs = get_file_storage();
        $context = \context_system::instance();
        $cutoff = time() - (self::RETENTION_DAYS * DAYSECS);

        $deletedcount = 0;

        foreach (['coursebackups', 'coursebackupzip'] as $filearea) {
            $files = $fs->get_area_files($context->id, 'local_easyadmin', $filearea, false, 'itemid', false);

            foreach ($files as $file) {
                if ($file->is_directory()) {
                    continue;
                }

                if ($file->get_timecreated() < $cutoff) {
                    $file->delete();
                    $deletedcount++;
                }
            }
        }

        mtrace('local_easyadmin: ' . $deletedcount . ' alte Kurs-Sicherung(en) gelöscht (älter als '
            . self::RETENTION_DAYS . ' Tage).');
    }
}
