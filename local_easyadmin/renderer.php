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
 * Renderer for local_easyadmin.
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class local_easyadmin_renderer extends plugin_renderer_base {

    /**
     * Rendert das Dashboard (Kachel-Übersicht oder Platzhalter-Teilseite).
     *
     * @param \local_easyadmin\output\dashboard $dashboard
     * @return string
     */
    public function render_dashboard(\local_easyadmin\output\dashboard $dashboard) {
        $data = $dashboard->export_for_template($this);
        return $this->render_from_template('local_easyadmin/dashboard', $data);
    }
}
