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

namespace local_easyadmin\privacy;

use core_privacy\local\metadata\collection;

/**
 * Privacy provider für local_easyadmin.
 *
 * Das Plugin speichert selbst keine dauerhaften Nutzerprofil-Daten. Die
 * Kachel "Kurse" > "Kurse sichern" erzeugt jedoch Kurs-Sicherungsdateien
 * (.mbz), die Teilnehmer/innen-Daten enthalten können, und legt diese
 * vorübergehend im eigenen Dateibereich des Plugins ab (component
 * local_easyadmin, Fileareas coursebackups/coursebackupzip) - technisch über
 * das core_files-Subsystem. Diese Dateien werden über eine geplante Aufgabe
 * automatisch nach 20 Tagen gelöscht (siehe
 * classes/task/cleanup_course_backups.php). Da es sich um eine reine,
 * zeitlich begrenzte Kopie ohnehin bereits über den jeweiligen Kurs
 * verfügbarer Daten handelt und core_files das Exportieren/Löschen bereits
 * generisch übernimmt, ist keine eigene Implementierung der
 * Export-/Löschanfragen-Schnittstellen erforderlich (siehe Moodle-
 * Entwickler-FAQ zur Privacy-API, Abschnitt "does send personal data into
 * ... another subsystem").
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\local\metadata\provider {
    /**
     * Beschreibt die vom Plugin genutzten Daten/Subsysteme.
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_subsystem_link(
            'core_files',
            [],
            'privacy:metadata:coursebackups'
        );

        return $collection;
    }
}
