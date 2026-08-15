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
 * Schul-Kacheln: Verwaltung der Startseiten-Kacheln (bis zu 8), inklusive
 * Anlegen/Bearbeiten/Löschen. Ersetzt die vormalige separate Verwaltung im
 * Plugin local_schooltiles über 64 einzelne Website-Administrations-
 * Rohfelder durch ein reguläres, geführtes moodleform.
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');
require_once(__DIR__ . '/lib.php');

require_login();

$context = context_system::instance();
require_capability('local/easyadmin:manageschooltiles', $context);

$dashboardurl = new moodle_url('/local/easyadmin/index.php');
$listurl = new moodle_url('/local/easyadmin/schooltiles.php');

$action = optional_param('action', 'list', PARAM_ALPHA);
$tilenumber = optional_param('tile', 0, PARAM_INT);

$maxtiles = 8;
$tilecount = (int) (get_config('local_easyadmin', 'schooltiles_count') ?: 0);

// -----------------------------------------------------------------------
// Löschen: einfache, sofortige Aktion (GET + Sesskey) - vertretbar, da es
// sich um reine Konfigurationsdaten (keine personenbezogenen/destruktiven
// Daten) handelt und die Kachel jederzeit neu angelegt werden kann.
if ($action === 'delete' && $tilenumber >= 1 && $tilenumber <= $tilecount) {
    require_sesskey();

    // Nachfolgende Kacheln um eine Position nach vorne rücken, damit keine
    // Lücke in der Nummerierung entsteht.
    for ($i = $tilenumber; $i < $tilecount; $i++) {
        foreach (['name', 'icon', 'color', 'textcolor', 'targettype', 'categoryid', 'url', 'cohortid'] as $field) {
            $value = get_config('local_easyadmin', 'schooltile' . ($i + 1) . '_' . $field);
            set_config('schooltile' . $i . '_' . $field, $value, 'local_easyadmin');
        }
        // Zugehöriges Bild ebenfalls eine Position nach vorne kopieren.
        $fs = get_file_storage();
        $fs->delete_area_files($context->id, 'local_easyadmin', 'schooltileimage', $i);
        $sourcefiles = $fs->get_area_files($context->id, 'local_easyadmin', 'schooltileimage', $i + 1, 'id', false);
        foreach ($sourcefiles as $sourcefile) {
            $fs->create_file_from_storedfile(
                [
                    'contextid' => $context->id,
                    'component' => 'local_easyadmin',
                    'filearea' => 'schooltileimage',
                    'itemid' => $i,
                    'filepath' => '/',
                    'filename' => $sourcefile->get_filename(),
                ],
                $sourcefile
            );
        }
    }

    // Letzte (jetzt doppelte) Kachel-Konfiguration entfernen.
    foreach (['name', 'icon', 'color', 'textcolor', 'targettype', 'categoryid', 'url', 'cohortid'] as $field) {
        unset_config('schooltile' . $tilecount . '_' . $field, 'local_easyadmin');
    }
    get_file_storage()->delete_area_files($context->id, 'local_easyadmin', 'schooltileimage', $tilecount);

    set_config('schooltiles_count', $tilecount - 1, 'local_easyadmin');

    redirect(
        $listurl,
        get_string('schooltile_deleted', 'local_easyadmin'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

// -----------------------------------------------------------------------
// Aktivieren/Deaktivieren: einfacher Umschalter direkt auf der Übersicht.
if ($action === 'toggle') {
    require_sesskey();
    $current = (bool) get_config('local_easyadmin', 'schooltiles_enabled');
    set_config('schooltiles_enabled', $current ? 0 : 1, 'local_easyadmin');
    redirect($listurl);
}

// -----------------------------------------------------------------------
// Anlegen/Bearbeiten über das moodleform.
if ($action === 'edit' || $action === 'add') {
    $editingnumber = $action === 'add' ? $tilecount + 1 : $tilenumber;

    if ($editingnumber < 1 || $editingnumber > $maxtiles) {
        redirect($listurl);
    }

    // Der Aufruf $PAGE->set_context() muss VOR der Erstellung des
    // moodleform stehen, da MoodleQuickForm beim Konstruieren bereits auf
    // $PAGE->context zugreift (sonst "Coding problem: $PAGE->context was
    // not set").
    $PAGE->set_url(new moodle_url('/local/easyadmin/schooltiles.php', ['action' => $action, 'tile' => $tilenumber]));
    $PAGE->set_context($context);
    $PAGE->set_pagelayout('report');
    $PAGE->set_title(get_string('tile_schooltiles_title', 'local_easyadmin'));
    $PAGE->set_heading(get_string('tile_schooltiles_title', 'local_easyadmin'));
    $PAGE->navbar->add(get_string('dashboardtitle', 'local_easyadmin'), $dashboardurl);
    $PAGE->navbar->add(get_string('tile_schooltiles_title', 'local_easyadmin'), $listurl);
    $PAGE->navbar->add(get_string('schooltile_editheader', 'local_easyadmin'));

    $mform = new \local_easyadmin\form\schooltile_form(
        new moodle_url('/local/easyadmin/schooltiles.php', ['action' => $action, 'tile' => $tilenumber])
    );

    $draftitemid = file_get_submitted_draft_itemid('image');
    file_prepare_draft_area(
        $draftitemid,
        $context->id,
        'local_easyadmin',
        'schooltileimage',
        $editingnumber,
        ['subdirs' => 0, 'maxfiles' => 1]
    );

    if ($mform->is_cancelled()) {
        redirect($listurl);
    } else if ($data = $mform->get_data()) {
        $prefix = 'schooltile' . $editingnumber . '_';
        set_config($prefix . 'name', $data->name, 'local_easyadmin');
        set_config($prefix . 'icon', $data->icon, 'local_easyadmin');
        set_config($prefix . 'color', $data->color, 'local_easyadmin');
        set_config($prefix . 'textcolor', $data->textcolor, 'local_easyadmin');
        set_config($prefix . 'targettype', $data->targettype, 'local_easyadmin');
        set_config($prefix . 'categoryid', (int) $data->categoryid, 'local_easyadmin');
        set_config($prefix . 'url', $data->url, 'local_easyadmin');
        set_config($prefix . 'cohortid', (int) $data->cohortid, 'local_easyadmin');

        file_save_draft_area_files(
            $data->image,
            $context->id,
            'local_easyadmin',
            'schooltileimage',
            $editingnumber,
            ['subdirs' => 0, 'maxfiles' => 1]
        );

        if ($action === 'add') {
            set_config('schooltiles_count', $editingnumber, 'local_easyadmin');
        }

        redirect(
            $listurl,
            get_string('schooltile_saved', 'local_easyadmin'),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    } else if ($action === 'edit') {
        $prefix = 'schooltile' . $editingnumber . '_';
        $currentdata = new stdClass();
        $currentdata->tilenumber = $editingnumber;
        $currentdata->name = get_config('local_easyadmin', $prefix . 'name');
        $currentdata->icon = get_config('local_easyadmin', $prefix . 'icon') ?: '📚';
        $currentdata->color = get_config('local_easyadmin', $prefix . 'color') ?: '#4A90E2';
        $currentdata->textcolor = get_config('local_easyadmin', $prefix . 'textcolor') ?: '#FFFFFF';
        $currentdata->targettype = get_config('local_easyadmin', $prefix . 'targettype') ?: 'category';
        $currentdata->categoryid = (int) get_config('local_easyadmin', $prefix . 'categoryid');
        $currentdata->url = get_config('local_easyadmin', $prefix . 'url') ?: '';
        $currentdata->cohortid = (int) get_config('local_easyadmin', $prefix . 'cohortid');
        $currentdata->image = $draftitemid;
        $mform->set_data($currentdata);
    }

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('schooltile_editheader', 'local_easyadmin'));
    $mform->display();
    echo $OUTPUT->footer();
    exit;
}

// -----------------------------------------------------------------------
// Zeigt die Übersicht (Standardansicht).
$PAGE->set_url($listurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('tile_schooltiles_title', 'local_easyadmin'));
$PAGE->set_heading(get_string('tile_schooltiles_title', 'local_easyadmin'));
$PAGE->navbar->add(get_string('dashboardtitle', 'local_easyadmin'), $dashboardurl);
$PAGE->navbar->add(get_string('tile_schooltiles_title', 'local_easyadmin'));

$enabled = (bool) get_config('local_easyadmin', 'schooltiles_enabled');
$fs = get_file_storage();

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('tile_schooltiles_title', 'local_easyadmin'));
echo html_writer::tag('p', get_string('schooltilesintro', 'local_easyadmin'), ['class' => 'text-muted']);

$toggleurl = new moodle_url('/local/easyadmin/schooltiles.php', ['action' => 'toggle', 'sesskey' => sesskey()]);
$togglelabel = $enabled
    ? get_string('schooltiles_disable', 'local_easyadmin')
    : get_string('schooltiles_enable', 'local_easyadmin');
echo html_writer::div(
    ($enabled
        ? html_writer::span(get_string('schooltiles_status_enabled', 'local_easyadmin'), 'badge badge-success mr-2')
        : html_writer::span(get_string('schooltiles_status_disabled', 'local_easyadmin'), 'badge badge-secondary mr-2'))
    . html_writer::link($toggleurl, $togglelabel, ['class' => 'btn btn-secondary']),
    'mb-3'
);

if ($tilecount === 0) {
    echo html_writer::div(get_string('schooltiles_none', 'local_easyadmin'), 'alert alert-info');
} else {
    echo html_writer::start_div('row');
    for ($i = 1; $i <= $tilecount; $i++) {
        $prefix = 'schooltile' . $i . '_';
        $name = get_config('local_easyadmin', $prefix . 'name');
        $icon = get_config('local_easyadmin', $prefix . 'icon') ?: '📚';
        $color = get_config('local_easyadmin', $prefix . 'color') ?: '#4A90E2';
        $textcolor = get_config('local_easyadmin', $prefix . 'textcolor') ?: '#FFFFFF';

        $imagefiles = $fs->get_area_files($context->id, 'local_easyadmin', 'schooltileimage', $i, 'id', false);
        $imagefile = reset($imagefiles);
        if ($imagefile) {
            $imageurl = \moodle_url::make_pluginfile_url(
                $context->id,
                'local_easyadmin',
                'schooltileimage',
                $i,
                '/',
                $imagefile->get_filename()
            )->out(false);
            $visualcontent = html_writer::empty_tag('img', [
                'src' => $imageurl,
                'alt' => '',
                'style' => 'max-width:100%;max-height:100%;',
            ]);
        } else {
            $visualcontent = html_writer::tag('span', s($icon), ['style' => 'font-size:2.5rem;']);
        }

        echo html_writer::start_div('col-md-3 mb-3');
        echo html_writer::start_div('card h-100');
        echo html_writer::div(
            $visualcontent,
            'd-flex align-items-center justify-content-center',
            ['style' => 'background:' . s($color) . ';height:100px;']
        );
        echo html_writer::start_div('card-body text-center p-2');
        echo html_writer::tag('p', s($name), ['class' => 'mb-2', 'style' => 'font-weight:500;']);

        $editurl = new moodle_url('/local/easyadmin/schooltiles.php', ['action' => 'edit', 'tile' => $i]);
        $deleteurl = new moodle_url('/local/easyadmin/schooltiles.php', [
            'action' => 'delete', 'tile' => $i, 'sesskey' => sesskey(),
        ]);
        echo html_writer::link(
            $editurl,
            get_string('schooltile_edit', 'local_easyadmin'),
            ['class' => 'btn btn-sm btn-secondary mr-1']
        );
        echo html_writer::link($deleteurl, get_string('schooltile_delete', 'local_easyadmin'), [
            'class' => 'btn btn-sm btn-outline-danger',
            'data-confirm-message' => get_string('schooltile_delete_confirm', 'local_easyadmin'),
        ]);
        echo html_writer::end_div();
        echo html_writer::end_div();
        echo html_writer::end_div();
    }
    echo html_writer::end_div();
}

if ($tilecount < $maxtiles) {
    $addurl = new moodle_url('/local/easyadmin/schooltiles.php', ['action' => 'add']);
    echo html_writer::link($addurl, get_string('schooltile_add', 'local_easyadmin'), ['class' => 'btn btn-primary']);
} else {
    echo html_writer::div(get_string('schooltiles_maxreached', 'local_easyadmin'), 'alert alert-info');
}

// Einfache Bestätigung vor dem Löschen (rein clientseitiger Komfort - die
// eigentliche Absicherung ist require_sesskey() serverseitig oben).
echo html_writer::script("
    document.querySelectorAll('[data-confirm-message]').forEach(function(link) {
        link.addEventListener('click', function(event) {
            if (!confirm(link.getAttribute('data-confirm-message'))) {
                event.preventDefault();
            }
        });
    });
");

echo html_writer::div(
    html_writer::link($dashboardurl, get_string('backtodashboard', 'local_easyadmin')),
    'mt-3'
);

echo $OUTPUT->footer();
