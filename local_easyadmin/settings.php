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
 * Admin settings tree entry for local_easyadmin.
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig || has_capability('local/easyadmin:view', context_system::instance())) {

    // Der Haupt-Einstiegspunkt erscheint automatisch als Menüpunkt in der
    // primären Kopfzeilen-Navigation (siehe classes/hook_callbacks.php,
    // Hook core\hook\output\before_http_headers). Diese Kategorie hier dient
    // zusätzlich als Fallback/Alternativzugang über die Website-Administration
    // (Tab "Allgemein"), z. B. falls Hooks durch ein Theme/Plugin deaktiviert
    // wurden oder zum Auffinden über die Admin-Suche.
    $ADMIN->add('root', new admin_category(
        'local_easyadmin_category',
        get_string('pluginname', 'local_easyadmin')
    ));

    $ADMIN->add('local_easyadmin_category', new admin_externalpage(
        'local_easyadmin_dashboard',
        get_string('dashboardtitle', 'local_easyadmin'),
        new moodle_url('/local/easyadmin/index.php'),
        'local/easyadmin:view'
    ));
}

// Verhindert, dass Moodle hier automatisch einen Standard-Settings-Link erzeugt.
$settings = null;
