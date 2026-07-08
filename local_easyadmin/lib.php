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

/**
 * Library functions for local_easyadmin.
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Fügt einen gut sichtbaren Einstiegspunkt in die primäre Navigation ein,
 * damit Schul-Admins das Dashboard nicht erst im Admin-Baum suchen müssen.
 *
 * @param global_navigation $navigation
 */
function local_easyadmin_extend_navigation(global_navigation $navigation) {
    $context = context_system::instance();

    if (!has_capability('local/easyadmin:view', $context)) {
        return;
    }

    $url = new moodle_url('/local/easyadmin/index.php');
    $node = navigation_node::create(
        get_string('dashboardtitle', 'local_easyadmin'),
        $url,
        navigation_node::TYPE_CUSTOM,
        null,
        'local_easyadmin',
        new pix_icon('i/settings', '')
    );
    $node->showinflatnavigation = true;

    $navigation->add_node($node);
}

/**
 * Liefert Dateien aus lokalen Dateibereichen aus (aktuell: Hintergrundbild).
 *
 * Das Hintergrundbild muss auch ohne Login abrufbar sein (z. B. auf der
 * Anmeldeseite), daher hier bewusst kein require_login()/Capability-Check -
 * es handelt sich um ein öffentlich sichtbares Design-Element, keine
 * schützenswerte Information.
 *
 * @param stdClass|null $course
 * @param stdClass|null $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function local_easyadmin_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    if ($context->contextlevel != CONTEXT_SYSTEM) {
        return false;
    }

    $publicareas = ['backgroundimage', 'welcomepopupimage', 'loginpageimage'];
    $protectedareas = ['coursebackups', 'coursebackupzip'];

    if (!in_array($filearea, $publicareas, true) && !in_array($filearea, $protectedareas, true)) {
        return false;
    }

    // Kurs-Sicherungen enthalten ggf. personenbezogene Daten (Teilnehmer/innen)
    // und sind deshalb - anders als Logo/Hintergrundbilder - nicht öffentlich
    // abrufbar, sondern erfordern dieselbe Berechtigung wie die Kurse-Kachel.
    if (in_array($filearea, $protectedareas, true)) {
        require_login();
        require_capability('local/easyadmin:managecourses', $context);
    }

    // Bei Hintergrund-/Popup-/Login-Bildern ist der Itemid immer 0; bei
    // Kurs-Sicherungen wird der Itemid genutzt (Kurs-ID bzw. Zeitstempel für
    // die gebündelte ZIP-Datei) und muss daher aus dem Pfad gelesen werden.
    if (in_array($filearea, $protectedareas, true)) {
        $itemid = (int) array_shift($args);
    } else {
        array_shift($args);
        $itemid = 0;
    }

    $filename = array_pop($args);
    $filepath = $args ? '/' . implode('/', $args) . '/' : '/';

    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'local_easyadmin', $filearea, $itemid, $filepath, $filename);
    if (!$file || $file->is_directory()) {
        return false;
    }

    send_stored_file($file, 0, 0, $forcedownload, $options);
    return true;
}

/**
 * Dupliziert einen Datei-Draft-Bereich in einen neuen, unabhängigen
 * Draft-Bereich.
 *
 * Wird benötigt, weil admin_setting_configstoredfile::write_setting() den
 * übergebenen Draft-Bereich beim Verarbeiten leert. Da das Logo sowohl für
 * 'core_admin/logo' als auch für 'core_admin/logocompact' (Header/
 * Navigationsleiste) verwendet werden soll, muss die hochgeladene Datei
 * dupliziert werden, bevor der erste der beiden Speichervorgänge läuft.
 *
 * @param int $sourcedraftitemid
 * @return int Neuer, unabhängiger Draft-Itemid mit denselben Dateien.
 */
function local_easyadmin_clone_draft_area(int $sourcedraftitemid): int {
    global $USER;

    $newdraftitemid = file_get_unused_draft_itemid();

    if (empty($sourcedraftitemid)) {
        return $newdraftitemid;
    }

    $fs = get_file_storage();
    $usercontext = context_user::instance($USER->id);
    $files = $fs->get_area_files($usercontext->id, 'user', 'draft', $sourcedraftitemid, 'id', false);

    foreach ($files as $file) {
        $fs->create_file_from_storedfile([
            'contextid' => $usercontext->id,
            'component' => 'user',
            'filearea'  => 'draft',
            'itemid'    => $newdraftitemid,
            'filepath'  => $file->get_filepath(),
            'filename'  => $file->get_filename(),
        ], $file);
    }

    return $newdraftitemid;
}

/**
 * Stellt sicher, dass der "Kursbeschreibung"-Block (course_summary) auf der
 * Startseite aktiv und sichtbar ist, damit die dort gespeicherte
 * "Informationen zur Website" auch tatsächlich irgendwo angezeigt wird.
 *
 * Zwei Schritte, da beide unabhängig voneinander nötig sind:
 * 1. Der Blocktyp selbst ist in Moodle 4.x/5.x-Neuinstallationen standardmäßig
 *    deaktiviert und muss aktiviert werden.
 * 2. Auch bei aktiviertem Blocktyp muss zusätzlich eine Instanz auf der
 *    Startseite existieren - beides passiert hier idempotent (kein Duplikat
 *    bei mehrfachem Speichern).
 *
 * Hinweis: Dies nutzt einen wenig dokumentierten Teil der Moodle-Block-API.
 * Schlägt einzelne Schritte fehl, wird dies bewusst nur protokolliert statt
 * das gesamte Speichern der Startseiten-Einstellungen abzubrechen - der
 * Hinweistext im Formular bleibt in jedem Fall als Rückfalloption bestehen.
 *
 * @return void
 */
function local_easyadmin_ensure_frontpage_summary_block(): void {
    global $DB;

    try {
        if (class_exists('\core\plugininfo\block')) {
            \core\plugininfo\block::enable_plugin('course_summary', 1);
        }
    } catch (\Throwable $e) {
        debugging('local_easyadmin: Konnte Block course_summary nicht aktivieren: ' . $e->getMessage());
    }

    try {
        $systemcontext = \context_system::instance();

        $exists = $DB->record_exists('block_instances', [
            'blockname' => 'course_summary',
            'parentcontextid' => $systemcontext->id,
            'pagetypepattern' => 'site-index',
        ]);

        if ($exists) {
            return;
        }

        $page = new \moodle_page();
        $page->set_context($systemcontext);
        $page->set_pagelayout('frontpage');
        $page->set_pagetype('site-index');
        $page->set_url('/index.php');
        $page->blocks->add_region('side-pre');
        $page->blocks->add_region('side-post');
        $page->blocks->add_block('course_summary', 'side-post', 1, false, 'site-index');
    } catch (\Throwable $e) {
        debugging('local_easyadmin: Konnte Block course_summary nicht auf der Startseite platzieren: '
            . $e->getMessage());
    }
}

/**
 * Sucht ein bestehendes, nicht gelöschtes Nutzerkonto anhand von
 * Anmeldename ODER E-Mail-Adresse.
 *
 * @param string $identifier Anmeldename oder E-Mail-Adresse.
 * @return stdClass|false Der Nutzerdatensatz oder false, falls nicht gefunden.
 */
function local_easyadmin_find_user_by_identifier(string $identifier) {
    global $DB, $CFG;

    $identifier = trim($identifier);
    if ($identifier === '') {
        return false;
    }

    $user = $DB->get_record('user', [
        'username' => \core_text::strtolower($identifier),
        'mnethostid' => $CFG->mnet_localhost_id,
        'deleted' => 0,
    ]);

    if (!$user) {
        $user = $DB->get_record('user', [
            'email' => $identifier,
            'mnethostid' => $CFG->mnet_localhost_id,
            'deleted' => 0,
        ]);
    }

    return $user ?: false;
}

/**
 * Schreibt eine Lehrkraft (Rolle "editingteacher") in einen Kurs ein und/
 * oder verknüpft eine Kohorte als automatisch synchronisierte
 * Teilnehmer/innen-Gruppe (Rolle "student").
 *
 * Nutzt ausschließlich offizielle Moodle-Core-/Plugin-APIs (manuelle
 * Einschreibung bzw. enrol_cohort-Plugin), keine direkten
 * Datenbank-Schreibzugriffe auf Einschreibetabellen.
 *
 * @param int $courseid
 * @param string $teacheridentifier Anmeldename/E-Mail der Lehrkraft, oder leer.
 * @param int $cohortid Kohorten-ID, oder 0 für keine Kohorten-Verknüpfung.
 * @return void
 */
function local_easyadmin_enrol_teacher_and_cohort(int $courseid, string $teacheridentifier, int $cohortid): void {
    global $DB;

    $coursecontext = \context_course::instance($courseid);

    $teacheridentifier = trim($teacheridentifier);
    if ($teacheridentifier !== '') {
        $teacher = local_easyadmin_find_user_by_identifier($teacheridentifier);
        $teacherrole = $DB->get_record('role', ['shortname' => 'editingteacher']);

        if ($teacher && $teacherrole) {
            $manualplugin = enrol_get_plugin('manual');
            $instance = $DB->get_record('enrol', ['courseid' => $courseid, 'enrol' => 'manual']);

            if ($manualplugin && $instance) {
                $manualplugin->enrol_user($instance, $teacher->id, $teacherrole->id);
            }
        }
    }

    if ($cohortid > 0 && $DB->record_exists('cohort', ['id' => $cohortid])) {
        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        $cohortplugin = enrol_get_plugin('cohort');

        if ($cohortplugin && $studentrole) {
            // Bereits vorhandene Kohorten-Verknüpfung für dieselbe Kohorte in
            // diesem Kurs nicht duplizieren.
            $existing = $DB->get_record('enrol', [
                'courseid' => $courseid,
                'enrol' => 'cohort',
                'customint1' => $cohortid,
            ]);

            if (!$existing) {
                $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
                $cohortplugin->add_instance($course, [
                    'customint1' => $cohortid,
                    'roleid' => $studentrole->id,
                ]);
            }
        }
    }
}
