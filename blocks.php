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
 * Blöcke: zusätzliche Block-Bereiche im Inhaltsbereich der Startseite und
 * der Kurs-Hauptseite aktivieren.
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
require_capability('local/easyadmin:manageblocks', $context);

$pageurl = new moodle_url('/local/easyadmin/blocks.php');
$dashboardurl = new moodle_url('/local/easyadmin/index.php');

$PAGE->set_url($pageurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('tile_blocks_title', 'local_easyadmin'));
$PAGE->set_heading(get_string('tile_blocks_title', 'local_easyadmin'));
$PAGE->navbar->add(get_string('dashboardtitle', 'local_easyadmin'), $dashboardurl);
$PAGE->navbar->add(get_string('tile_blocks_title', 'local_easyadmin'));

$mform = new \local_easyadmin\form\blocks_form($pageurl);

if ($mform->is_cancelled()) {
    redirect($dashboardurl);
} else if ($data = $mform->get_data()) {
    require_sesskey();
    foreach (LOCAL_EASYADMIN_BLOCK_AREAS as $area) {
        $property = 'blocks_' . $area;
        set_config($property, empty($data->$property) ? 0 : 1, 'local_easyadmin');
    }
    redirect(
        $pageurl,
        get_string('blocks_saved', 'local_easyadmin'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
} else {
    $currentdata = new stdClass();
    foreach (LOCAL_EASYADMIN_BLOCK_AREAS as $area) {
        $property = 'blocks_' . $area;
        $currentdata->$property = (int) !empty(get_config('local_easyadmin', $property));
    }
    $mform->set_data($currentdata);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('tile_blocks_title', 'local_easyadmin'));
echo html_writer::tag('p', get_string('blocks_intro', 'local_easyadmin'), ['class' => 'text-muted']);
$mform->display();
echo html_writer::tag('h3', get_string('blocks_howto_header', 'local_easyadmin'), ['class' => 'mt-4']);
echo html_writer::tag('p', get_string('blocks_howto', 'local_easyadmin'));
echo $OUTPUT->footer();
