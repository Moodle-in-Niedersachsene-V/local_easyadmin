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

namespace local_easyadmin;

use context_system;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

/**
 * Hook callbacks for local_easyadmin.
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {

    /**
     * Blendet einen Menüpunkt "Schul-Administration" in die primäre
     * Kopfzeilen-Navigation (Boost-Theme, "Zusätzliche Menüpunkte") ein -
     * ausschließlich für die Dauer des aktuellen Requests und nur für
     * Nutzer/innen mit der Capability local/easyadmin:view.
     *
     * Es wird bewusst NICHT in die Datenbank/Konfiguration geschrieben
     * (kein set_config()), damit manuell durch Admins gepflegte Einträge in
     * "Zusätzliche Menüpunkte" niemals überschrieben oder dauerhaft verändert
     * werden. Die Änderung an $CFG->custommenuitems gilt nur für diesen einen
     * Seitenaufruf.
     *
     * @param \core\hook\output\before_http_headers $hook
     * @return void
     */
    public static function before_http_headers(\core\hook\output\before_http_headers $hook): void {
        global $CFG;

        if (!isloggedin() || isguestuser()) {
            return;
        }

        if (!has_capability('local/easyadmin:view', context_system::instance())) {
            return;
        }

        $label = get_string('dashboardtitle', 'local_easyadmin');
        $url = (new moodle_url('/local/easyadmin/index.php'))->out(false);
        $entry = "{$label}|{$url}\n";

        // Voranstellen, damit bereits vorhandene, manuell gepflegte
        // Menüpunkte des Admins unverändert erhalten bleiben.
        $CFG->custommenuitems = $entry . ($CFG->custommenuitems ?? '');
    }

    /**
     * Gibt individuelles Design (Hintergrund, Textfarbe/-größe) als <style>
     * im Seitenkopf aus - für ALLE Nutzer/innen der Website, nicht nur für
     * Schul-Admins, da es sich um sichtbares Website-Design handelt.
     *
     * Bewusst über die Hook-API statt Theme-SCSS umgesetzt, damit bestehende
     * Theme-Einstellungen (z. B. individuelle Raw-SCSS eines Admins)
     * unangetastet bleiben und die Änderung sofort ohne Cache-Leerung wirkt.
     *
     * @param \core\hook\output\before_standard_head_html_generation $hook
     * @return void
     */
    public static function before_standard_head_html_generation(
        \core\hook\output\before_standard_head_html_generation $hook
    ): void {
        global $PAGE;

        $backgroundcolor = get_config('local_easyadmin', 'backgroundcolor');
        $textcolor = get_config('local_easyadmin', 'textcolor');
        $textsize = get_config('local_easyadmin', 'textsize');

        $colorpattern = '/^#[0-9A-Fa-f]{6}$/';
        $context = context_system::instance();
        $fs = get_file_storage();

        $rules = [];

        if (!(empty($backgroundcolor) && empty($textcolor) && empty($textsize))) {
            $backgroundimageurl = null;
            $files = $fs->get_area_files($context->id, 'local_easyadmin', 'backgroundimage', 0, 'itemid', false);
            if ($files) {
                $file = reset($files);
                $backgroundimageurl = \moodle_url::make_pluginfile_url(
                    $context->id,
                    'local_easyadmin',
                    'backgroundimage',
                    0,
                    $file->get_filepath(),
                    $file->get_filename()
                )->out();
            }

            $sizemap = [
                'small'  => '14px',
                'normal' => '16px',
                'large'  => '18px',
                'xlarge' => '20px',
            ];

            if ($backgroundimageurl !== null) {
                // Hintergrundbild hat Vorrang vor der Hintergrundfarbe.
                $rules[] = 'body { background-image: url("' . $backgroundimageurl . '"); '
                    . 'background-size: cover; background-position: center; background-attachment: fixed; }';
            } else if (!empty($backgroundcolor) && preg_match($colorpattern, $backgroundcolor)) {
                $rules[] = 'body { background-color: ' . $backgroundcolor . '; }';
            }

            if (!empty($textcolor) && preg_match($colorpattern, $textcolor)) {
                $rules[] = '#region-main, #region-main a, #page-content { color: ' . $textcolor . '; }';
            }

            if (!empty($textsize) && isset($sizemap[$textsize])) {
                $rules[] = '#region-main, #page-content { font-size: ' . $sizemap[$textsize] . '; }';
            }
        }

        if (!empty($rules)) {
            $hook->add_html('<style id="local_easyadmin_design">' . implode(' ', $rules) . '</style>');
        }

        // Anmeldeseiten-Überschreibung (ersetzt das seit Moodle 5.2 mitgelieferte
        // Standard-KI-Bild). Nur auf der Anmeldeseite selbst aktiv.
        $onloginpage = (strpos((string) $PAGE->pagetype, 'login-') === 0);
        if ($onloginpage && !empty(get_config('local_easyadmin', 'loginpage_hidedefaultimage'))) {
            self::output_loginpage_override($hook, $context, $fs);
        }
    }

    /**
     * Überschreibt/entfernt das Standard-Anmeldeseiten-Bild.
     *
     * Da die genaue HTML-Struktur der seit Moodle 5.2 neu gestalteten
     * Anmeldeseite von Version zu Version variieren kann, wird bewusst mit
     * mehreren, möglichst robusten CSS-Regeln UND einem vorsichtigen
     * JavaScript-Fallback gearbeitet, der das Bild-Element anhand seines
     * sichtbaren Bildunterschrift-Textes ("KI-generiertes Bild"/"AI-generated
     * image") sucht. Der JS-Teil ist bewusst defensiv (try/catch, keine
     * Fehler bei abweichender Struktur) und verändert nichts, wenn nichts
     * gefunden wird.
     *
     * @param \core\hook\output\before_standard_head_html_generation $hook
     * @param \context_system $context
     * @param \file_storage $fs
     * @return void
     */
    protected static function output_loginpage_override(
        \core\hook\output\before_standard_head_html_generation $hook,
        \context_system $context,
        \file_storage $fs
    ): void {
        $customimageurl = null;
        $files = $fs->get_area_files($context->id, 'local_easyadmin', 'loginpageimage', 0, 'itemid', false);
        if ($files) {
            $file = reset($files);
            $customimageurl = \moodle_url::make_pluginfile_url(
                $context->id,
                'local_easyadmin',
                'loginpageimage',
                0,
                $file->get_filepath(),
                $file->get_filename()
            )->out();
        }

        // CSS-Overrides mit hoher Spezifität/!important, da Moodles eigene
        // Login-Styles erfahrungsgemäß sehr bestimmt sind.
        $css = 'body.pagelayout-login { background-image: none !important; }';
        if ($customimageurl !== null) {
            $css .= ' body.pagelayout-login { background-image: url("' . $customimageurl . '") !important; '
                . 'background-size: cover !important; background-position: center !important; }';
        }

        $hook->add_html('<style id="local_easyadmin_loginpage">' . $css . '</style>');

        // JS-Fallback: Bildunterschrift-Text suchen und das umgebende
        // Bild-Panel ausblenden bzw. das Bild darin austauschen.
        $customimagejson = $customimageurl !== null ? json_encode($customimageurl) : 'null';
        $hook->add_html('<script>(function() {'
            . 'try {'
            . '  var captions = ["KI-generiertes Bild", "AI-generated image"];'
            . '  var walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);'
            . '  var node, target = null;'
            . '  while ((node = walker.nextNode())) {'
            . '    var text = (node.textContent || "").trim();'
            . '    if (captions.indexOf(text) !== -1) {'
            . '      target = node.parentElement;'
            . '      break;'
            . '    }'
            . '  }'
            . '  if (!target) { return; }'
            . '  var customimage = ' . $customimagejson . ';'
            . '  var panel = target.closest("div, section, aside") || target;'
            . '  var img = panel.querySelector ? panel.querySelector("img") : null;'
            . '  if (customimage && img) {'
            . '    img.src = customimage;'
            . '    target.style.display = "none";'
            . '  } else {'
            . '    panel.style.display = "none";'
            . '  }'
            . '} catch (e) { /* Struktur unbekannt - keine Änderung vornehmen. */ }'
            . '})();</script>');
    }

    /**
     * Blendet ein optionales Begrüßungs-Popup ein, z. B. für nicht
     * angemeldete Besucher/innen einer Testinstanz.
     *
     * Wird nur angezeigt, wenn im Design-Bereich aktiviert. Die Anzeige
     * beschränkt sich bewusst auf nicht angemeldete Nutzer/innen (inkl.
     * Gäste), da der Standard-Button "Jetzt anmelden" für bereits
     * angemeldete Personen keinen Sinn ergibt.
     *
     * @param \core\hook\output\before_standard_top_of_body_html_generation $hook
     * @return void
     */
    public static function before_standard_top_of_body_html_generation(
        \core\hook\output\before_standard_top_of_body_html_generation $hook
    ): void {
        global $PAGE;

        if (empty(get_config('local_easyadmin', 'welcomepopup_enabled'))) {
            return;
        }

        if (isloggedin() && !isguestuser()) {
            return;
        }

        // Auf der Anmeldeseite selbst (und verwandten Login-Unterseiten wie
        // Passwort vergessen) macht ein "Jetzt anmelden"-Button keinen Sinn -
        // er würde auf dieselbe Seite zurückführen. Das Popup an sich bleibt
        // aber sichtbar, da nicht angemeldete Besucher/innen bei erzwungener
        // Anmeldung oft AUSSCHLIESSLICH die Login-Seite zu sehen bekommen -
        // ein komplettes Ausblenden hier würde das Popup faktisch nie zeigen.
        $onloginpage = (strpos((string) $PAGE->pagetype, 'login-') === 0);

        $title = get_config('local_easyadmin', 'welcomepopup_title');
        $message = get_config('local_easyadmin', 'welcomepopup_message');
        $buttontext = get_config('local_easyadmin', 'welcomepopup_buttontext');
        $buttonurlraw = get_config('local_easyadmin', 'welcomepopup_buttonurl');
        $autoclosesecs = get_config('local_easyadmin', 'welcomepopup_autoclose');
        $autoclosesecs = ($autoclosesecs === false || $autoclosesecs === '') ? 15 : (int) $autoclosesecs;

        if (trim((string) $title) === '') {
            return;
        }

        try {
            $buttonurl = new \moodle_url($buttonurlraw !== '' ? $buttonurlraw : '/login/index.php');
        } catch (\moodle_exception $e) {
            $buttonurl = new \moodle_url('/login/index.php');
        }

        // Popup-Bild: eigener Upload wie beim Logo, sonst Emoji als Fallback.
        $context = context_system::instance();
        $fs = get_file_storage();
        $imagehtml = '<div class="local-easyadmin-welcome-icon" aria-hidden="true">🏫</div>';
        $imagefiles = $fs->get_area_files($context->id, 'local_easyadmin', 'welcomepopupimage', 0, 'itemid', false);
        if ($imagefiles) {
            $imagefile = reset($imagefiles);
            $imageurl = \moodle_url::make_pluginfile_url(
                $context->id,
                'local_easyadmin',
                'welcomepopupimage',
                0,
                $imagefile->get_filepath(),
                $imagefile->get_filename()
            )->out();
            $imagehtml = '<img class="local-easyadmin-welcome-image" src="' . $imageurl . '" alt="">';
        }

        $html = '<div id="local-easyadmin-welcome-overlay" class="local-easyadmin-welcome-overlay" '
            . 'role="dialog" aria-modal="true" aria-labelledby="local-easyadmin-welcome-title" hidden>'
            . '<div class="local-easyadmin-welcome-modal">'
            . '<button type="button" class="local-easyadmin-welcome-close" aria-label="'
            . get_string('closebuttontitle', 'core') . '">&times;</button>'
            . $imagehtml
            . '<h2 id="local-easyadmin-welcome-title">' . s($title) . '</h2>';

        if (trim((string) $message) !== '') {
            $html .= '<p>' . nl2br(s($message)) . '</p>';
        }

        if (trim((string) $buttontext) !== '' && !$onloginpage) {
            $html .= '<a class="btn btn-primary local-easyadmin-welcome-btn" href="'
                . $buttonurl->out(false) . '">' . s($buttontext) . '</a>';
        }

        $html .= '</div></div>';

        // Minimal-JavaScript ohne Abhängigkeiten: einblenden, per Klick auf
        // X oder auf den Hintergrund schließen, automatisches Schließen nach
        // einigen Sekunden für Fälle, in denen niemand reagiert, UND
        // sofortiges Merken als "erledigt" beim Klick auf den
        // Anmelden-Button - damit das Popup nicht auf der direkt
        // anschließenden Anmeldeseite erneut (unnötig) auftaucht. Merken nur
        // für die aktuelle Browser-Sitzung (sessionStorage).
        $html .= '<script>(function() {'
            . 'try { if (sessionStorage.getItem("local_easyadmin_welcome_dismissed") === "1") { return; } } catch (e) {}'
            . 'var overlay = document.getElementById("local-easyadmin-welcome-overlay");'
            . 'if (!overlay) { return; }'
            . 'overlay.hidden = false;'
            . 'var autoclosetimer = null;'
            . 'var close = function() {'
            . '  overlay.hidden = true;'
            . '  if (autoclosetimer) { clearTimeout(autoclosetimer); }'
            . '  try { sessionStorage.setItem("local_easyadmin_welcome_dismissed", "1"); } catch (e) {}'
            . '};'
            . 'var closebtn = overlay.querySelector(".local-easyadmin-welcome-close");'
            . 'if (closebtn) { closebtn.addEventListener("click", close); }'
            . 'overlay.addEventListener("click", function(ev) { if (ev.target === overlay) { close(); } });'
            . 'var loginbtn = overlay.querySelector(".local-easyadmin-welcome-btn");'
            . 'if (loginbtn) {'
            . '  loginbtn.addEventListener("click", function() {'
            . '    try { sessionStorage.setItem("local_easyadmin_welcome_dismissed", "1"); } catch (e) {}'
            . '  });'
            . '}'
            . ($autoclosesecs > 0 ? 'autoclosetimer = setTimeout(close, ' . ($autoclosesecs * 1000) . ');' : '')
            . '})();</script>';

        $hook->add_html($html);
    }
}
