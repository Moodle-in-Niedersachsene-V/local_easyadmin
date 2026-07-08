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

namespace local_easyadmin\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;
use context_system;
use moodle_url;

/**
 * Renderable für das easyadmin-Dashboard.
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dashboard implements renderable, templatable {

    /** @var string Angeforderter Teilbereich, leer = Übersicht. */
    protected $section;

    /**
     * @param string $section
     */
    public function __construct(string $section = '') {
        $this->section = $section;
    }

    /**
     * Definiert die verfügbaren Kacheln inkl. benötigter Capability.
     *
     * @return array
     */
    protected function get_tiles(): array {
        return [
            [
                'key' => 'design',
                'capability' => 'local/easyadmin:managedesign',
                'emoji' => '🎨',
                'colorclass' => 'ea-tile-design',
            ],
            [
                'key' => 'news',
                'capability' => 'local/easyadmin:managenews',
                'emoji' => '🏠',
                'colorclass' => 'ea-tile-news',
            ],
            [
                'key' => 'users',
                'capability' => 'local/easyadmin:manageusers',
                'emoji' => '👥',
                'colorclass' => 'ea-tile-users',
            ],
            [
                'key' => 'courses',
                'capability' => 'local/easyadmin:managecourses',
                'emoji' => '📚',
                'colorclass' => 'ea-tile-courses',
            ],
            [
                'key' => 'communication',
                'capability' => 'local/easyadmin:managecommunication',
                'emoji' => '💬',
                'colorclass' => 'ea-tile-communication',
            ],
            [
                'key' => 'privacy',
                'capability' => 'local/easyadmin:manageprivacy',
                'emoji' => '🔒',
                'colorclass' => 'ea-tile-privacy',
            ],
            [
                'key' => 'maintenance',
                'capability' => 'local/easyadmin:managemaintenance',
                'emoji' => '🛠️',
                'colorclass' => 'ea-tile-maintenance',
            ],
        ];
    }

    /**
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        global $CFG;

        $context = context_system::instance();
        $validsections = array_column($this->get_tiles(), 'key');

        // Ungültige oder nicht berechtigte Teilbereiche fallen auf die Übersicht zurück.
        if ($this->section !== '' && in_array($this->section, $validsections, true)) {
            $tile = null;
            foreach ($this->get_tiles() as $candidate) {
                if ($candidate['key'] === $this->section) {
                    $tile = $candidate;
                    break;
                }
            }

            if ($tile !== null && has_capability($tile['capability'], $context)) {
                return [
                    'showplaceholder' => true,
                    'sectiontitle' => get_string('tile_' . $tile['key'] . '_title', 'local_easyadmin'),
                    'placeholdertext' => get_string('placeholder_comingsoon', 'local_easyadmin'),
                    'backurl' => (new moodle_url('/local/easyadmin/index.php'))->out(false),
                    'backlabel' => get_string('backtodashboard', 'local_easyadmin'),
                ];
            }
        }

        $tiles = [];
        foreach ($this->get_tiles() as $tile) {
            if (!has_capability($tile['capability'], $context)) {
                continue;
            }

            // Design, Startseite, Nutzer & Gruppen, Kurse, Sicherheit &
            // Datenschutz sowie Wartung & Berichte haben bereits echte
            // Funktionalität, alle anderen Kacheln zeigen weiterhin die
            // Platzhalterseite.
            if ($tile['key'] === 'design') {
                $url = (new moodle_url('/local/easyadmin/design.php'))->out(false);
            } else if ($tile['key'] === 'news') {
                $url = (new moodle_url('/local/easyadmin/frontpage.php'))->out(false);
            } else if ($tile['key'] === 'users') {
                $url = (new moodle_url('/local/easyadmin/users.php'))->out(false);
            } else if ($tile['key'] === 'courses') {
                $url = (new moodle_url('/local/easyadmin/courses.php'))->out(false);
            } else if ($tile['key'] === 'privacy') {
                $url = (new moodle_url('/local/easyadmin/security.php'))->out(false);
            } else if ($tile['key'] === 'maintenance') {
                $url = (new moodle_url('/local/easyadmin/maintenance.php'))->out(false);
            } else {
                $url = (new moodle_url('/local/easyadmin/index.php', ['section' => $tile['key']]))->out(false);
            }

            $tiles[] = [
                'title' => get_string('tile_' . $tile['key'] . '_title', 'local_easyadmin'),
                'description' => get_string('tile_' . $tile['key'] . '_desc', 'local_easyadmin'),
                'emoji' => $tile['emoji'],
                'colorclass' => $tile['colorclass'],
                'url' => $url,
            ];
        }

        return [
            'showplaceholder' => false,
            'welcometitle' => get_string('welcometitle', 'local_easyadmin'),
            'welcomeintro' => get_string('welcomeintro', 'local_easyadmin'),
            'tiles' => $tiles,
            'hastiles' => !empty($tiles),
            'nopermissiontext' => get_string('nopermissiontiles', 'local_easyadmin'),
        ];
    }
}
