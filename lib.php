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
    $publicitemidareas = ['schooltileimage'];
    $protectedareas = ['coursebackups', 'coursebackupzip'];

    $allareas = array_merge($publicareas, $publicitemidareas, $protectedareas);
    if (!in_array($filearea, $allareas, true)) {
        return false;
    }

    // Kurs-Sicherungen enthalten ggf. personenbezogene Daten (Teilnehmer/innen)
    // und sind deshalb - anders als Logo/Hintergrundbilder/Schul-Kachel-Bilder -
    // nicht öffentlich abrufbar, sondern erfordern dieselbe Berechtigung wie
    // die Kurse-Kachel.
    if (in_array($filearea, $protectedareas, true)) {
        require_login();
        require_capability('local/easyadmin:managecourses', $context);
    }

    // Bei Hintergrund-/Popup-/Login-Bildern ist der Itemid immer 0; bei
    // Kurs-Sicherungen und Schul-Kachel-Bildern wird der Itemid genutzt
    // (Kurs-ID/Zeitstempel bzw. Kachel-Nummer) und muss daher aus dem Pfad
    // gelesen werden.
    if (in_array($filearea, $protectedareas, true) || in_array($filearea, $publicitemidareas, true)) {
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

/**
 * Liefert das HTML-Markup für einen eigenen Farbverlauf-Farbwähler zu einem
 * bestehenden Text-Eingabefeld (2D-Fläche für Sättigung/Helligkeit plus
 * Farbton-Leiste), analog zu Moodles admin_setting_configcolourpicker.
 *
 * Bewusst als eigenes, leichtgewichtiges Widget umgesetzt statt auf Moodles
 * internen, YUI-basierten Farbwähler-Mechanismus (M.util.init_colour_picker)
 * zurückzugreifen: YUI gilt in aktuellen Moodle-Versionen als veraltet und
 * eine formslib-eigene Entsprechung existiert laut offiziellem
 * Moodle-Tracker-Eintrag (MDL-59586) bis heute nicht.
 *
 * @param string $inputid Die HTML-ID des zugehörigen Text-Eingabefelds
 *                         (bei moodleform-Feldern standardmäßig "id_" + Feldname).
 * @return string
 */
function local_easyadmin_colourpicker_markup(string $inputid): string {
    return \html_writer::div(
        \html_writer::div('', 'ea-cp-square') .
        \html_writer::div('', 'ea-cp-hue'),
        'local-easyadmin-colourpicker',
        ['data-target' => $inputid]
    );
}

/**
 * Liefert das gemeinsame JavaScript für alle Farbverlauf-Farbwähler auf
 * einer Seite. Reines Vanilla-JavaScript, keine Abhängigkeit von jQuery,
 * YUI oder einem AMD-Modul.
 *
 * @return string
 */
function local_easyadmin_colourpicker_script(): string {
    return <<<'JS'
<script>
(function() {
    if (window.eaColourPickerInitDone) {
        return;
    }
    window.eaColourPickerInitDone = true;

    function hsvToHex(h, s, v) {
        var c = v * s;
        var x = c * (1 - Math.abs((h / 60) % 2 - 1));
        var m = v - c;
        var r = 0, g = 0, b = 0;
        if (h < 60) { r = c; g = x; } else if (h < 120) { r = x; g = c; }
        else if (h < 180) { g = c; b = x; } else if (h < 240) { g = x; b = c; }
        else if (h < 300) { r = x; b = c; } else { r = c; b = x; }
        var toHex = function(n) {
            var v2 = Math.round((n + m) * 255);
            return ('0' + v2.toString(16)).slice(-2);
        };
        return '#' + toHex(r) + toHex(g) + toHex(b);
    }

    function hexToHsv(hex) {
        hex = (hex || '').replace('#', '');
        if (hex.length !== 6) {
            hex = '4a90e2';
        }
        var r = parseInt(hex.substr(0, 2), 16) / 255;
        var g = parseInt(hex.substr(2, 2), 16) / 255;
        var b = parseInt(hex.substr(4, 2), 16) / 255;
        var max = Math.max(r, g, b), min = Math.min(r, g, b);
        var d = max - min;
        var h = 0;
        if (d !== 0) {
            if (max === r) { h = 60 * (((g - b) / d) % 6); }
            else if (max === g) { h = 60 * ((b - r) / d + 2); }
            else { h = 60 * ((r - g) / d + 4); }
        }
        if (h < 0) { h += 360; }
        var s = max === 0 ? 0 : d / max;
        var v = max;
        return { h: h, s: s, v: v };
    }

    function initPicker(widget) {
        var targetId = widget.getAttribute('data-target');
        var input = document.getElementById(targetId);
        if (!input) {
            return;
        }
        var square = widget.querySelector('.ea-cp-square');
        var hue = widget.querySelector('.ea-cp-hue');

        var squareCursor = document.createElement('div');
        squareCursor.className = 'ea-cp-cursor';
        square.appendChild(squareCursor);

        var hueCursor = document.createElement('div');
        hueCursor.className = 'ea-cp-hue-cursor';
        hue.appendChild(hueCursor);

        var state = hexToHsv(input.value);

        function render() {
            square.style.backgroundColor = hsvToHex(state.h, 1, 1);
            squareCursor.style.left = (state.s * 100) + '%';
            squareCursor.style.top = ((1 - state.v) * 100) + '%';
            hueCursor.style.top = (state.h / 360 * 100) + '%';
        }

        function updateFromState() {
            var hex = hsvToHex(state.h, state.s, state.v);
            input.value = hex;
            input.dispatchEvent(new Event('change'));
            render();
        }

        function pointerToRatio(evt, el) {
            var rect = el.getBoundingClientRect();
            var clientX = evt.touches ? evt.touches[0].clientX : evt.clientX;
            var clientY = evt.touches ? evt.touches[0].clientY : evt.clientY;
            var x = Math.min(Math.max((clientX - rect.left) / rect.width, 0), 1);
            var y = Math.min(Math.max((clientY - rect.top) / rect.height, 0), 1);
            return { x: x, y: y };
        }

        function bindDrag(el, handler) {
            var dragging = false;
            var move = function(evt) {
                if (!dragging) {
                    return;
                }
                handler(pointerToRatio(evt, el));
                evt.preventDefault();
            };
            el.addEventListener('mousedown', function(evt) {
                dragging = true;
                move(evt);
            });
            el.addEventListener('touchstart', function(evt) {
                dragging = true;
                move(evt);
            });
            document.addEventListener('mousemove', move);
            document.addEventListener('touchmove', move);
            document.addEventListener('mouseup', function() { dragging = false; });
            document.addEventListener('touchend', function() { dragging = false; });
        }

        bindDrag(square, function(ratio) {
            state.s = ratio.x;
            state.v = 1 - ratio.y;
            updateFromState();
        });

        bindDrag(hue, function(ratio) {
            state.h = ratio.y * 360;
            updateFromState();
        });

        input.addEventListener('input', function() {
            state = hexToHsv(input.value);
            render();
        });

        render();
    }

    document.querySelectorAll('.local-easyadmin-colourpicker').forEach(initPicker);
})();
</script>
JS;
}

/**
 * Minimale Aufbewahrungsfrist in Tagen für die automatische Löschung
 * inaktiver Nutzerkonten. Verhindert eine versehentliche Fehlkonfiguration
 * auf einen zu niedrigen, riskanten Wert.
 */
define('LOCAL_EASYADMIN_INACTIVEDELETION_MINDAYS', 90);

/**
 * Ermittelt alle Nutzerkonten, die seit mindestens der angegebenen Anzahl
 * Tage inaktiv sind und für die automatische/manuelle Löschung infrage
 * kommen. Website-Administrator/innen, das Gastkonto sowie bereits
 * gelöschte Konten werden grundsätzlich nie zurückgegeben.
 *
 * Als "letzte Aktivität" gilt der jeweils späteste der drei Zeitstempel
 * lastaccess/firstaccess/timecreated, damit frisch angelegte Konten ohne
 * bisherige Anmeldung (lastaccess = 0) nicht fälschlich als "seit jeher
 * inaktiv" erfasst werden.
 *
 * @param int $days
 * @return array Liste von stdClass-Objekten (id, firstname, lastname, username, lastaccess, firstaccess, timecreated).
 */
function local_easyadmin_get_inactive_users(int $days): array {
    global $DB, $CFG;

    // Der jeweils späteste Zeitstempel wird bewusst in PHP statt per
    // datenbankspezifischer SQL-Funktion ermittelt (GREATEST existiert nicht
    // einheitlich über alle von Moodle unterstützten Datenbanktreiber hinweg
    // als portable $DB-Methode).
    $cutoff = time() - ($days * DAYSECS);

    $allusers = $DB->get_records_select(
        'user',
        'deleted = 0 AND id <> :guestid',
        ['guestid' => $CFG->siteguest],
        'lastname ASC, firstname ASC',
        'id, firstname, lastname, username, lastaccess, firstaccess, timecreated'
    );

    $siteadminids = array_filter(array_map('trim', explode(',', $CFG->siteadmins)));

    return array_filter($allusers, static function ($user) use ($siteadminids, $cutoff) {
        if (in_array((string) $user->id, $siteadminids, true)) {
            return false;
        }
        $lastactivity = max($user->lastaccess, $user->firstaccess, $user->timecreated);
        return $lastactivity < $cutoff;
    });
}

/** Erlaubte Kachelgrößen der Schul-Kacheln. */
define('LOCAL_EASYADMIN_SCHOOLTILES_SIZES', ['small', 'medium', 'large', 'xlarge']);

/** Erlaubte Ausrichtungen der Schul-Kacheln. */
define('LOCAL_EASYADMIN_SCHOOLTILES_ALIGNS', ['left', 'center', 'right']);

/**
 * Liefert die gespeicherte Kachelgröße (nur Werte aus der Positivliste).
 *
 * @return string small, medium oder large
 */
function local_easyadmin_get_schooltiles_size(): string {
    $value = (string) get_config('local_easyadmin', 'schooltiles_size');
    return in_array($value, LOCAL_EASYADMIN_SCHOOLTILES_SIZES, true) ? $value : 'medium';
}

/**
 * Liefert die gespeicherte Ausrichtung (nur Werte aus der Positivliste).
 *
 * @return string left, center oder right
 */
function local_easyadmin_get_schooltiles_align(): string {
    $value = (string) get_config('local_easyadmin', 'schooltiles_align');
    return in_array($value, LOCAL_EASYADMIN_SCHOOLTILES_ALIGNS, true) ? $value : 'left';
}

/** Schlüssel der zusätzlichen Block-Bereiche (Seite_Position). */
define('LOCAL_EASYADMIN_BLOCK_AREAS', ['frontpage_top', 'frontpage_bottom', 'course_top', 'course_bottom']);

/** Maximale Anzahl Hintergrundbilder der Anmeldeseite. */
define('LOCAL_EASYADMIN_LOGINPAGE_MAXIMAGES', 10);

/** Erlaubte Wechselintervalle der Anmeldeseiten-Bilder in Sekunden (0 = kein Wechsel). */
define('LOCAL_EASYADMIN_LOGINPAGE_INTERVALS', [0, 5, 10, 15, 30, 60]);

/** Erlaubte Positionen des Anmeldefensters. */
define('LOCAL_EASYADMIN_LOGINPAGE_POSITIONS', ['left', 'center', 'right']);
