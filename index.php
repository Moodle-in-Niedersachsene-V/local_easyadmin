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
 * Vereinfachtes Administrations-Dashboard für Schul-Admins.
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

$context = context_system::instance();
require_capability('local/easyadmin:view', $context);

// Optionaler Deep-Link in einen Teilbereich, z. B. index.php?section=design.
// Solange die Teilseiten noch nicht existieren, wird ein "in Arbeit"-Hinweis angezeigt.
$section = optional_param('section', '', PARAM_ALPHA);

$PAGE->set_url(new moodle_url('/local/easyadmin/index.php', $section !== '' ? ['section' => $section] : []));
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('dashboardtitle', 'local_easyadmin'));
$PAGE->set_heading(get_string('dashboardtitle', 'local_easyadmin'));

// Breadcrumb: Zurück zur Startseite bewusst einfach halten.
$PAGE->navbar->add(get_string('dashboardtitle', 'local_easyadmin'));

echo $OUTPUT->header();

$renderer = $PAGE->get_renderer('local_easyadmin');
$dashboard = new \local_easyadmin\output\dashboard($section);
echo $renderer->render($dashboard);

echo $OUTPUT->footer();
