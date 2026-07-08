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
 * Nutzer & Gruppen: Mini-Kachel-Übersicht, die zu den Unterseiten
 * "Nutzer anlegen" und "Globale Gruppen" führt - analog zur Kurse-Kachel.
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

require_login();

$context = context_system::instance();
require_capability('local/easyadmin:manageusers', $context);

$dashboardurl = new moodle_url('/local/easyadmin/index.php');

$PAGE->set_url(new moodle_url('/local/easyadmin/users.php'));
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('tile_users_title', 'local_easyadmin'));
$PAGE->set_heading(get_string('tile_users_title', 'local_easyadmin'));
$PAGE->navbar->add(get_string('dashboardtitle', 'local_easyadmin'), $dashboardurl);
$PAGE->navbar->add(get_string('tile_users_title', 'local_easyadmin'));

$subtiles = [
    [
        'url' => new moodle_url('/local/easyadmin/usercreate.php'),
        'emoji' => '🧑‍🎓',
        'colorclass' => 'ea-tile-users',
        'title' => get_string('createusersheader', 'local_easyadmin'),
        'desc' => get_string('subtile_createusers_desc', 'local_easyadmin'),
    ],
    [
        'url' => new moodle_url('/local/easyadmin/cohorts.php'),
        'emoji' => '👨‍👩‍👧‍👦',
        'colorclass' => 'ea-tile-communication',
        'title' => get_string('cohortsheader', 'local_easyadmin'),
        'desc' => get_string('subtile_cohorts_desc', 'local_easyadmin'),
    ],
];

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('tile_users_title', 'local_easyadmin'));
echo html_writer::tag('p', get_string('usersdashboardintro', 'local_easyadmin'), ['class' => 'text-muted']);

echo html_writer::start_div('local-easyadmin-dashboard');
echo html_writer::start_div('ea-tile-grid');
foreach ($subtiles as $tile) {
    echo html_writer::start_tag('a', ['href' => $tile['url']->out(false), 'class' => 'ea-tile ' . $tile['colorclass']]);
    echo html_writer::tag('span', $tile['emoji'], ['class' => 'ea-tile-icon', 'aria-hidden' => 'true']);
    echo html_writer::tag('span', $tile['title'], ['class' => 'ea-tile-title']);
    echo html_writer::tag('span', $tile['desc'], ['class' => 'ea-tile-desc']);
    echo html_writer::end_tag('a');
}
echo html_writer::end_div();
echo html_writer::end_div();

echo html_writer::div(
    html_writer::link($dashboardurl, get_string('backtodashboard', 'local_easyadmin')),
    'mt-3'
);

echo $OUTPUT->footer();
