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

        // Während eines Plugin-Upgrades ist die neue version.php bereits
        // eingelesen, die Capabilities aus db/access.php sind aber u. U. noch
        // nicht in die Datenbank geschrieben. has_capability() würde in diesem
        // Moment die Debug-Meldung "Capability ... was not found!" auslösen
        // (siehe Upgrade-/Umgebungsseite). get_capability_info() prüft ohne
        // Debug-Warnung, ob die Capability überhaupt schon registriert ist.
        if (!get_capability_info('local/easyadmin:view')) {
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

        // Maximale Seiten-/Inhaltsbreite (unabhängig von den Farb-/Text-
        // Einstellungen, daher außerhalb des obigen Blocks). Nur bekannte,
        // sichere Werte werden akzeptiert; leerer/unbekannter Wert = keine
        // Regel = Moodle-Standardbreite bleibt unverändert.
        $pagewidth = get_config('local_easyadmin', 'page_width');
        $allowedwidths = ['1200px', '1400px', '100%'];
        if (!empty($pagewidth) && in_array($pagewidth, $allowedwidths, true)) {
            // Neben dem Inhaltsbereich auch Seitenkopf, Fußleiste und klebende
            // Fußleiste anpassen, die Boost auf dieselbe schmale Breite begrenzt.
            $rules[] = '#page-content, .course-content, #page.container-fluid, .main-inner, '
                . '.header-maxwidth, .footer-popover, .sticky-footer-content '
                . '{ max-width: ' . $pagewidth . ' !important; margin-left: auto; margin-right: auto; }';
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
        // Der Aufruf clean_param(PARAM_URL) entfernt gefährliche Schemata
        // (z. B. javascript:) - auch bei evtl. vor dieser Absicherung
        // gespeicherten Alt-Werten. Defense-in-Depth zusätzlich zur
        // Speicher-/Formularprüfung.
        $buttonurlraw = clean_param(
            (string) get_config('local_easyadmin', 'welcomepopup_buttonurl'),
            PARAM_URL
        );
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

    /**
     * Zeigt die konfigurierten Schul-Kacheln unterhalb der Startseite an.
     *
     * Ersetzt die frühere Darstellung im separaten Plugin local_schooltiles.
     * Korrigiert dabei mehrere dort gefundene Probleme: doppelte statt
     * dreifache Mustache-Klammern (verhindert ungefilterte Ausgabe),
     * addEventListener statt Inline-onclick-Attributen, sowie eine
     * Beschränkung auf die tatsächliche Startseite (vorher plugin-weit auf
     * jeder Seite aktiv).
     *
     * @param \core\hook\output\before_footer_html_generation $hook
     * @return void
     */
    public static function render_schooltiles(\core\hook\output\before_footer_html_generation $hook): void {
        global $CFG, $PAGE, $DB, $USER, $OUTPUT;

        if ($PAGE->pagetype !== 'site-index') {
            return;
        }

        if (empty(get_config('local_easyadmin', 'schooltiles_enabled'))) {
            return;
        }

        require_once($CFG->dirroot . '/local/easyadmin/lib.php');

        $context = context_system::instance();
        $tilecount = (int) (get_config('local_easyadmin', 'schooltiles_count') ?: 0);
        if ($tilecount === 0) {
            return;
        }

        $canmanage = has_capability('local/easyadmin:manageschooltiles', $context);
        $fs = get_file_storage();
        $tiles = [];

        for ($i = 1; $i <= $tilecount; $i++) {
            $prefix = 'schooltile' . $i . '_';
            $cohortid = (int) get_config('local_easyadmin', $prefix . 'cohortid');

            if ($cohortid > 0 && !$canmanage) {
                $ismember = $DB->record_exists('cohort_members', ['cohortid' => $cohortid, 'userid' => $USER->id]);
                if (!$ismember) {
                    continue;
                }
            }

            $targettype = get_config('local_easyadmin', $prefix . 'targettype') ?: 'category';
            if ($targettype === 'category') {
                $categoryid = (int) get_config('local_easyadmin', $prefix . 'categoryid');
                if (!$categoryid || !$DB->record_exists('course_categories', ['id' => $categoryid])) {
                    continue;
                }
                $url = (new moodle_url('/course/index.php', ['categoryid' => $categoryid]))->out(false);
            } else {
                $rawurl = get_config('local_easyadmin', $prefix . 'url');
                if (empty($rawurl)) {
                    continue;
                }
                try {
                    $url = (new moodle_url($rawurl))->out(false);
                } catch (\moodle_exception $e) {
                    continue;
                }
            }

            $imagefiles = $fs->get_area_files($context->id, 'local_easyadmin', 'schooltileimage', $i, 'id', false);
            $imagefile = reset($imagefiles);
            if ($imagefile) {
                $imageurl = moodle_url::make_pluginfile_url(
                    $context->id,
                    'local_easyadmin',
                    'schooltileimage',
                    $i,
                    '/',
                    $imagefile->get_filename()
                )->out(false);
            } else {
                $imageurl = null;
            }

            $tiles[] = [
                'name' => get_config('local_easyadmin', $prefix . 'name'),
                'icon' => $imageurl ? null : (get_config('local_easyadmin', $prefix . 'icon') ?: '📚'),
                'imageurl' => $imageurl,
                'color' => get_config('local_easyadmin', $prefix . 'color') ?: '#4A90E2',
                'textcolor' => get_config('local_easyadmin', $prefix . 'textcolor') ?: '#FFFFFF',
                'url' => $url,
            ];
        }

        if (empty($tiles)) {
            return;
        }

        $templatecontext = [
            'tiles' => $tiles,
            'canmanage' => $canmanage,
            'manageurl' => (new moodle_url('/local/easyadmin/schooltiles.php'))->out(false),
            'sizeclass' => 'local-easyadmin-size-' . local_easyadmin_get_schooltiles_size(),
            'alignclass' => 'local-easyadmin-align-' . local_easyadmin_get_schooltiles_align(),
        ];

        $html = $OUTPUT->render_from_template('local_easyadmin/schooltiles', $templatecontext);
        $hook->add_html($html);
    }

    /** @var array Aktive zusätzliche Block-Bereiche dieses Seitenaufrufs (Position => Regionsname). */
    protected static $activeblockregions = [];

    /**
     * Meldet die zusätzlichen Block-Bereiche (Inhaltsbereich oben/unten) bei
     * Moodles Block-Verwaltung an, sofern sie für den aktuellen Seitentyp
     * (Startseite bzw. Kurs-Hauptseite) aktiviert wurden.
     *
     * Der Hook läuft zu Beginn von header(), also noch bevor Moodle die
     * Blöcke lädt - nur dann dürfen zusätzliche Regionen registriert werden.
     *
     * @param \core\hook\output\before_http_headers $hook
     * @return void
     */
    public static function register_block_regions(\core\hook\output\before_http_headers $hook): void {
        global $PAGE;

        self::$activeblockregions = [];

        if (during_initial_install() || !get_capability_info('local/easyadmin:manageblocks')) {
            return;
        }

        $pagetype = (string) $PAGE->pagetype;
        if ($pagetype === 'site-index' && $PAGE->pagelayout === 'frontpage') {
            $prefix = 'frontpage';
        } else if (strpos($pagetype, 'course-view-') === 0 && $PAGE->pagelayout === 'course') {
            $prefix = 'course';
        } else {
            return;
        }

        foreach (['top', 'bottom'] as $position) {
            if (empty(get_config('local_easyadmin', 'blocks_' . $prefix . '_' . $position))) {
                continue;
            }
            $region = 'easyadmin-' . $position;
            try {
                $PAGE->blocks->add_region($region);
            } catch (\coding_exception $e) {
                // Blöcke wurden bereits geladen: Bereich kann nicht mehr ergänzt werden.
                return;
            }
            self::$activeblockregions[$position] = $region;
        }
    }

    /**
     * Gibt den Block-Bereich "Inhaltsbereich oben" aus.
     *
     * @param \core\hook\output\before_standard_top_of_body_html_generation $hook
     * @return void
     */
    public static function render_block_region_top(
        \core\hook\output\before_standard_top_of_body_html_generation $hook
    ): void {
        if (isset(self::$activeblockregions['top'])) {
            $hook->add_html(self::block_region_html('top'));
        }
    }

    /**
     * Gibt den Block-Bereich "Inhaltsbereich unten" aus und verschiebt beide
     * Bereiche per kleinem Skript in den Inhaltsbereich (#region-main).
     *
     * Ohne Skript bleiben die Bereiche an ihrer Ausgabestelle (ganz oben
     * bzw. oberhalb der Fußzeile) und sind weiterhin nutzbar.
     *
     * @param \core\hook\output\before_footer_html_generation $hook
     * @return void
     */
    public static function render_block_region_bottom(\core\hook\output\before_footer_html_generation $hook): void {
        if (empty(self::$activeblockregions)) {
            return;
        }

        if (isset(self::$activeblockregions['bottom'])) {
            $hook->add_html(self::block_region_html('bottom'));
        }

        $hook->add_html('<script>(function() {'
            . 'var main = document.getElementById("region-main");'
            . 'if (!main) { return; }'
            . 'var top = document.getElementById("local-easyadmin-blocks-top");'
            . 'if (top) { main.insertBefore(top, main.firstChild); }'
            . 'var bottom = document.getElementById("local-easyadmin-blocks-bottom");'
            . 'if (bottom) { main.appendChild(bottom); }'
            . '})();</script>');
    }

    /**
     * Erzeugt das HTML eines zusätzlichen Block-Bereichs.
     *
     * Im Bearbeitungsmodus wird zusätzlich die Schaltfläche "Block hinzufügen"
     * für genau diesen Bereich angezeigt. Ohne Blöcke und außerhalb des
     * Bearbeitungsmodus wird nichts ausgegeben.
     *
     * @param string $position top oder bottom
     * @return string
     */
    protected static function block_region_html(string $position): string {
        global $PAGE, $OUTPUT;

        $region = self::$activeblockregions[$position];
        $editing = $PAGE->user_is_editing() && $PAGE->user_can_edit_blocks();
        if (!$editing && !$PAGE->blocks->region_has_content($region, $OUTPUT)) {
            return '';
        }

        return \html_writer::div(
            ($editing ? $OUTPUT->addblockbutton($region) : '') . $OUTPUT->custom_block_region($region),
            'local-easyadmin-blockregion local-easyadmin-blockregion-' . $position,
            ['id' => 'local-easyadmin-blocks-' . $position]
        );
    }
}
