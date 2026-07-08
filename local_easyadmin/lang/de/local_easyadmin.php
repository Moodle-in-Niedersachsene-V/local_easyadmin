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
 * German strings for local_easyadmin.
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Einfache Schul-Administration';
$string['dashboardtitle'] = 'Schul-Administration';
$string['welcometitle'] = 'Willkommen in der Schul-Administration';
$string['welcomeintro'] = 'Hier können Sie die wichtigsten Einstellungen Ihres Moodles bearbeiten – ganz ohne Vorkenntnisse. Wählen Sie einfach einen Bereich aus.';
$string['backtodashboard'] = 'Zurück zur Übersicht';
$string['placeholder_comingsoon'] = 'Dieser Bereich wird gerade eingerichtet und steht in Kürze zur Verfügung.';
$string['nopermissiontiles'] = 'Für Ihr Konto sind aktuell keine Bereiche freigeschaltet. Bitte wenden Sie sich an Moodle in Niedersachsen e. V., falls Sie Zugriff benötigen.';

$string['tile_design_title'] = 'Design';
$string['tile_design_desc'] = 'Logo, Farben und Erscheinungsbild anpassen';
$string['tile_users_title'] = 'Nutzer & Gruppen';
$string['tile_users_desc'] = 'Lehrkräfte, Schüler*innen und Gruppen verwalten';
$string['tile_courses_title'] = 'Kurse';
$string['tile_courses_desc'] = 'Kurse anlegen, verwalten und archivieren';
$string['tile_news_title'] = 'Startseite';
$string['tile_news_desc'] = 'Name, Kurzbezeichnung, Beschreibung und Anzeige der Startseite';
$string['tile_communication_title'] = 'Kommunikation';
$string['tile_communication_desc'] = 'E-Mail und Ankündigungen (folgt in Kürze)';
$string['tile_privacy_title'] = 'Sicherheit und Datenschutz';
$string['tile_privacy_desc'] = 'Wichtige Sicherheits- und Datenschutz-Einstellungen prüfen und anpassen';
$string['tile_maintenance_title'] = 'Wartung & Berichte';
$string['tile_maintenance_desc'] = 'Debug-Modus, Caches leeren, einfache Zugriffszahlen';

// Capability-Strings.
$string['easyadmin:view'] = 'Schul-Administrations-Dashboard aufrufen';
$string['easyadmin:managedesign'] = 'Design-Einstellungen über das Dashboard ändern';
$string['easyadmin:manageusers'] = 'Nutzer/Gruppen über das Dashboard verwalten';
$string['easyadmin:managecourses'] = 'Kurse über das Dashboard verwalten';
$string['easyadmin:managenews'] = 'Startseiten-Einstellungen über das Dashboard verwalten';
$string['easyadmin:managecommunication'] = 'Kommunikationsfunktionen über das Dashboard verwalten';
$string['easyadmin:manageprivacy'] = 'Sicherheits- und Datenschutz-Einstellungen über das Dashboard verwalten';
$string['easyadmin:managemaintenance'] = 'Wartungsfunktionen und Berichte über das Dashboard nutzen';

// Privacy-API (keine personenbezogenen Daten in diesem Plugin gespeichert).
$string['privacy:metadata'] = 'Das Plugin „Einfache Schul-Administration“ speichert selbst keine personenbezogenen Daten. Es dient ausschließlich als vereinfachte Oberfläche für bestehende Moodle-Kernfunktionen.';

// Design-Formular.
$string['designintro'] = 'Passen Sie das Erscheinungsbild Ihres Moodles an. Änderungen wirken sich sofort auf die gesamte Website aus.';
$string['designsaved'] = 'Die Design-Einstellungen wurden gespeichert.';
$string['field_logo'] = 'Schul-Logo';
$string['field_logo_help'] = 'Wird im Kopfbereich, auf der Anmeldeseite und in E-Mails/PDF-Dokumenten angezeigt. Empfohlen: PNG mit transparentem Hintergrund.';
$string['backgroundimage'] = 'Hintergrundbild';
$string['backgroundimage_help'] = 'Wird als Hintergrund der gesamten Moodle-Website angezeigt. Überschreibt die Hintergrundfarbe, solange ein Bild hinterlegt ist.';
$string['backgroundcolor'] = 'Hintergrundfarbe (falls kein Hintergrundbild)';
$string['backgroundcolor_help'] = 'Wird nur verwendet, solange kein Hintergrundbild hochgeladen wurde.';
$string['textcolor'] = 'Textfarbe';
$string['textsize'] = 'Textgröße';
$string['textsize_small'] = 'Klein';
$string['textsize_normal'] = 'Standard';
$string['textsize_large'] = 'Groß';
$string['textsize_xlarge'] = 'Sehr groß';
$string['error_invalidcolor'] = 'Bitte eine gültige Farbe im Format #RRGGBB angeben.';

// Anmeldeseite.
$string['loginpageheader'] = 'Anmeldeseite';
$string['loginpage_hidedefaultimage'] = 'Standard-Bild auf der Anmeldeseite ausblenden/ersetzen';
$string['loginpage_hidedefaultimage_help'] = 'Blendet das von Moodle standardmäßig angezeigte Bild auf der Anmeldeseite aus. Wird zusätzlich ein eigenes Bild hochgeladen, wird dieses stattdessen angezeigt. Hinweis: Je nach Moodle-Version kann die genaue Wirkung leicht variieren, da sich die Anmeldeseite technisch weiterentwickelt.';
$string['loginpage_image'] = 'Eigenes Bild für die Anmeldeseite';
$string['loginpage_image_help'] = 'Ersetzt das Standardbild auf der Anmeldeseite durch ein eigenes Bild, z. B. ein Schulfoto.';

// Begrüßungs-Popup.
$string['welcomepopupheader'] = 'Begrüßungs-Popup';
$string['welcomepopup_enabled'] = 'Begrüßungs-Popup anzeigen';
$string['welcomepopup_enabled_help'] = 'Zeigt beim Seitenaufruf ein Willkommensfenster für noch nicht angemeldete Besucher/innen an, z. B. mit Hinweis auf eine Testversion und einem Anmelden-Button. Erscheint nicht auf der Anmeldeseite selbst.';
$string['welcomepopup_image'] = 'Bild/Icon';
$string['welcomepopup_image_help'] = 'Optionales Bild im Popup, z. B. ein Schul-Logo oder Maskottchen. Wird kein Bild hochgeladen, erscheint stattdessen ein Schul-Symbol.';
$string['welcomepopup_title'] = 'Titel';
$string['welcomepopup_title_default'] = 'Willkommen!';
$string['welcomepopup_message'] = 'Nachricht';
$string['welcomepopup_message_default'] = 'Dies ist eine Testversion der Grundschul-Lernplattform.';
$string['welcomepopup_buttontext'] = 'Button-Text';
$string['welcomepopup_buttontext_default'] = 'Jetzt anmelden';
$string['welcomepopup_buttonurl'] = 'Button-Ziel';
$string['welcomepopup_buttonurl_help'] = 'Wohin der Button führen soll. Standard: die Anmeldeseite (/login/index.php). Es sind nur Adressen auf dieser Website erlaubt.';
$string['welcomepopup_autoclose'] = 'Automatisch schließen nach (Sekunden)';
$string['welcomepopup_autoclose_help'] = 'Nach dieser Zeit schließt sich das Popup von selbst, falls niemand reagiert. 0 = nie automatisch schließen (nur per Klick auf X, Hintergrund oder Anmelden-Button).';
$string['error_autoclose_range'] = 'Bitte einen Wert zwischen 0 und 120 Sekunden angeben.';

// Startseiten-Einstellungen.
$string['frontpageintro'] = 'Grundlegende Angaben zu Ihrer Website sowie was Besucher/innen vor bzw. nach der Anmeldung auf der Startseite sehen.';
$string['frontpagesaved'] = 'Die Startseiten-Einstellungen wurden gespeichert.';
$string['frontpagesummary_easyadmin'] = 'Informationen zur Website';
$string['frontpagesummary_easyadmin_help'] = 'Kurzer Beschreibungstext zu Ihrer Schule/Website. Hinweis: Hier ist nur einfacher Text ohne Formatierung möglich; für Bilder oder Formatierungen weiterhin Website-Administration > Allgemein > Startseite nutzen.';
$string['summary_blockhint'] = 'Wichtig: Dieser Text wird nur sichtbar, wenn der Block "Kursbeschreibung" auf der Startseite eingeblendet ist. Mit dem Häkchen unten sorgen wir automatisch dafür.';
$string['summary_showblock'] = 'Block "Kursbeschreibung" automatisch auf der Startseite anzeigen';
$string['summary_showblock_help'] = 'Aktiviert den Block "Kursbeschreibung" (falls nötig) und platziert ihn auf der Startseite, damit die oben eingegebenen Informationen zur Website für Besucher/innen sichtbar werden. Falls bereits ein solcher Block vorhanden ist, wird kein zweiter hinzugefügt. Hinweis: Position und Reihenfolge der Blöcke lassen sich anschließend weiterhin ganz normal über den Bearbeiten-Modus der Startseite anpassen.';

// Sicherheit und Datenschutz.
$string['securityintro'] = 'Wichtige Sicherheits- und Datenschutz-Einstellungen für Ihre Website an einem Ort.';
$string['securitysaved'] = 'Die Sicherheits- und Datenschutz-Einstellungen wurden gespeichert.';
$string['securityheader'] = 'Sicherheit';
$string['privacyheader'] = 'Datenschutz';
$string['forcelogin_easyadmin'] = 'Anmeldung für die gesamte Website erzwingen';
$string['forcelogin_easyadmin_help'] = 'Wenn aktiviert, können nicht angemeldete Besucher/innen keinerlei Inhalte der Website sehen (auch nicht die Startseite) - sie werden direkt zur Anmeldeseite geleitet.';
$string['guestloginbutton_easyadmin'] = 'Gastzugang-Button auf der Anmeldeseite anzeigen';
$string['guestloginbutton_easyadmin_help'] = 'Zeigt auf der Anmeldeseite einen zusätzlichen Button, über den sich Besucher/innen ohne eigenes Konto als Gast anmelden können (mit eingeschränkten Rechten).';
$string['passwordpolicy_easyadmin'] = 'Passwortregeln aktivieren';
$string['passwordpolicy_easyadmin_help'] = 'Erzwingt bei neuen bzw. geänderten Passwörtern eine Mindestlänge (siehe Feld darunter).';
$string['minpasswordlength_easyadmin'] = 'Mindestlänge des Passworts';
$string['minpassworddigits_easyadmin'] = 'Ziffern';
$string['minpassworddigits_easyadmin_help'] = 'Passwörter müssen mindestens die angegebene Zahl von Ziffern enthalten.';
$string['minpasswordlower_easyadmin'] = 'Kleinbuchstaben';
$string['minpasswordlower_easyadmin_help'] = 'Passwörter müssen mindestens die angegebene Zahl von Kleinbuchstaben enthalten.';
$string['minpasswordupper_easyadmin'] = 'Großbuchstaben';
$string['minpasswordupper_easyadmin_help'] = 'Passwörter müssen mindestens die angegebene Zahl von Großbuchstaben enthalten.';
$string['minpasswordnonalphanum_easyadmin'] = 'Sonderzeichen';
$string['minpasswordnonalphanum_easyadmin_help'] = 'Passwörter müssen mindestens die angegebene Zahl von Sonderzeichen enthalten.';
$string['maxconsecutiveidentchars_easyadmin'] = 'Aufeinanderfolgende identische Zeichen';
$string['maxconsecutiveidentchars_easyadmin_help'] = 'Passwörter dürfen maximal diese Zahl aufeinanderfolgender gleicher Zeichen haben. Der Wert 0 deaktiviert diese Prüfung.';
$string['error_passwordpart_range'] = 'Bitte einen Wert zwischen 0 und 20 angeben.';

// Wartung & Berichte.
$string['maintenanceheader'] = 'Fehlersuche (Debug-Modus)';
$string['maintenancesaved'] = 'Die Wartungs-Einstellungen wurden gespeichert.';
$string['debugmode_warning'] = 'Wichtig: Der Debug-Modus zeigt zusätzliche technische Informationen an, die für die Fehlersuche hilfreich sind, aber auch sensible Details preisgeben können. Bitte nach der Fehlersuche unbedingt wieder deaktivieren.';
$string['debugmode_enabled'] = 'Debug-Modus aktivieren';
$string['debugmode_enabled_help'] = 'Zeigt ausführliche technische Fehlermeldungen auf der Website an. Nur zur gezielten Fehlersuche kurzzeitig aktivieren und danach wieder ausschalten.';
$string['debugmode_active_notice'] = 'Achtung: Der Debug-Modus ist aktuell aktiv. Bitte denken Sie daran, ihn nach der Fehlersuche wieder zu deaktivieren.';
$string['cachesheader'] = 'Caches leeren';
$string['cachesintro'] = 'Falls Änderungen (z. B. am Design) nicht sofort sichtbar werden, kann das Leeren der Caches helfen.';
$string['purgecachesbutton'] = 'Caches jetzt leeren';
$string['cachespurged'] = 'Die Caches wurden geleert.';
$string['reportsheader'] = 'Berichte';
$string['reportsintro'] = 'Ein kurzer Überblick über die Nutzung Ihrer Website in den letzten 7 Tagen.';
$string['accessescount_easyadmin'] = 'Zugriffe (letzte 7 Tage): {$a}';
$string['activeuserscount_easyadmin'] = 'Aktive Nutzer/innen (letzte 7 Tage): {$a}';
$string['accessescount_unavailable'] = 'Zugriffszahlen sind auf dieser Website aktuell nicht verfügbar (Standard-Logspeicher nicht aktiv).';
$string['viewfulllog_easyadmin'] = 'Alle Logdaten ansehen';
$string['viewfulllog_easyadmin_notice'] = 'Hinweis: Für die vollständige Log-Ansicht wird zusätzlich zu den Rechten dieses Dashboards die Berechtigung "report/log:view" benötigt.';

// Nutzer & Gruppen.
$string['createusersheader'] = 'Neues Nutzerkonto anlegen';
$string['createusersintro'] = 'Legt ein neues, manuelles Nutzerkonto an (keine Anmeldung über SSO/LDAP o. ä.).';
$string['manualaccount_notice'] = 'Dieses Formular legt ausschließlich manuelle Konten an - also Konten mit eigenem Anmeldenamen und Passwort, unabhängig von einer eventuell vorhandenen Single-Sign-On-Anmeldung.';
$string['username_easyadmin'] = 'Anmeldename';
$string['username_easyadmin_help'] = 'Nur Kleinbuchstaben, Ziffern sowie die Zeichen . - _ @ sind erlaubt.';
$string['password_easyadmin'] = 'Passwort';
$string['password_easyadmin_help'] = 'Muss die auf dieser Website hinterlegte Passwortrichtlinie erfüllen (siehe Kachel "Sicherheit und Datenschutz").';
$string['forcepasswordchange_easyadmin'] = 'Passwort bei erster Anmeldung ändern lassen';
$string['makeadmin_warning'] = 'Achtung: Diese Person erhält damit vollen Administratorzugriff auf die gesamte Website. Bitte nur vergeben, wenn wirklich nötig. Administrator-Konten sollten aus Sicherheitsgründen immer manuelle Konten sein (kein SSO) - das ist bei einem über dieses Formular angelegten Konto automatisch der Fall.';
$string['makeadmin_easyadmin'] = 'Diese Person zum Website-Administrator/zur Website-Administratorin machen';
$string['makeadmin_easyadmin_help'] = 'Nur sichtbar, wenn Sie selbst bereits vollen Administratorzugriff haben. Verleiht der neuen Person dieselben, uneingeschränkten Rechte wie Ihnen selbst.';
$string['createuser_easyadmin'] = 'Nutzerkonto anlegen';
$string['usercreated_easyadmin'] = 'Das Nutzerkonto für {$a} wurde angelegt.';
$string['error_invalidusername'] = 'Der Anmeldename darf nur Kleinbuchstaben, Ziffern sowie . - _ @ enthalten.';
$string['error_usernametaken'] = 'Dieser Anmeldename ist bereits vergeben.';
$string['error_invalidemail'] = 'Bitte eine gültige E-Mail-Adresse angeben.';
$string['error_emailtaken'] = 'Diese E-Mail-Adresse wird bereits von einem anderen Konto verwendet.';

$string['cohortsheader'] = 'Globale Gruppen';
$string['cohortsintro'] = 'Kohorten sind Gruppen, die kursübergreifend gelten - z. B. eine ganze Klasse, die in mehreren Kursen gleichzeitig Mitglied ist.';
$string['cohortcount_easyadmin'] = 'Aktuell vorhandene globale Gruppen: {$a}';
$string['cohortname_easyadmin'] = 'Name der Gruppe';
$string['cohortidnumber_easyadmin'] = 'Kennung (optional)';
$string['cohortidnumber_easyadmin_help'] = 'Eine eindeutige interne Kennung, z. B. für den Import/Export. Kann leer gelassen werden.';
$string['cohortdescription_easyadmin'] = 'Beschreibung (optional)';
$string['createcohort_easyadmin'] = 'Globale Gruppe anlegen';
$string['cohortcreated_easyadmin'] = 'Die globale Gruppe "{$a}" wurde angelegt.';
$string['error_cohortidnumbertaken'] = 'Diese Kennung wird bereits verwendet.';
$string['managecohorts_easyadmin'] = 'Globale Gruppen vollständig verwalten (Mitglieder hinzufügen, Import etc.)';
$string['managecohorts_easyadmin_notice'] = 'Hinweis: Für die vollständige Verwaltung wird zusätzlich zu den Rechten dieses Dashboards die Berechtigung "moodle/cohort:manage" benötigt.';

// Kurse: Kategorien.
$string['categoriesheader'] = 'Kategorien erstellen';
$string['categoriesintro'] = 'Kategorien helfen, Kurse übersichtlich zu ordnen - z. B. nach Jahrgangsstufe oder Fach.';
$string['categoryname_easyadmin'] = 'Name der Kategorie';
$string['categoryparent_easyadmin'] = 'Übergeordnete Kategorie';
$string['categoryparent_top_easyadmin'] = 'Keine (oberste Ebene)';
$string['categorydescription_easyadmin'] = 'Beschreibung (optional)';
$string['createcategory_easyadmin'] = 'Kategorie anlegen';
$string['categorycreated_easyadmin'] = 'Die Kategorie "{$a}" wurde angelegt.';
$string['categorycount_easyadmin'] = 'Aktuell vorhandene Kategorien: {$a}';

// Kurse: Kategorien - mehrere auf einmal.
$string['bulkcategoryheader'] = 'Mehrere Unterkategorien auf einmal anlegen';
$string['bulkcategoryintro'] = 'Praktisch z. B. für eine Unterkategorie je Lehrkraft-Kürzel unter einer übergeordneten Kategorie "Lehrkräfte" - einfach ein Kürzel/Name pro Zeile eintragen.';
$string['bulkcategory_parent_easyadmin'] = 'Übergeordnete Kategorie';
$string['bulkcategory_parent_easyadmin_help'] = 'Alle unten eingetragenen Namen werden als Unterkategorien dieser Kategorie angelegt.';
$string['bulkcategory_names_easyadmin'] = 'Namen (ein Eintrag pro Zeile)';
$string['bulkcategory_names_easyadmin_help'] = 'Ein Name/Kürzel pro Zeile, z. B.:
AM
BC
DE
Bereits vorhandene Unterkategorien mit demselben Namen unter derselben übergeordneten Kategorie werden übersprungen, es entstehen also keine Dubletten.';
$string['bulkcategory_create_button'] = 'Kategorien anlegen';
$string['bulkcategory_created_easyadmin'] = '{$a->created} Kategorie(n) wurden angelegt, {$a->skipped} bereits vorhandene übersprungen.';
$string['error_bulkcategory_toomany'] = 'Bitte maximal 200 Einträge auf einmal eingeben.';

// Kurse anlegen.
$string['createcoursesheader'] = 'Kurse anlegen';
$string['createcoursesintro'] = 'Legt einen neuen Kurs an.';
$string['coursecategory_easyadmin'] = 'Kategorie';
$string['coursevisible_easyadmin'] = 'Für Teilnehmer/innen sichtbar';
$string['createcourse_easyadmin'] = 'Kurs anlegen';
$string['coursecreated_easyadmin'] = 'Der Kurs "{$a}" wurde angelegt.';
$string['error_shortnametaken_easyadmin'] = 'Diese Kurzbezeichnung wird bereits von einem anderen Kurs verwendet.';

$string['bulkcourseheader'] = 'Mehrere Kurse auf einmal anlegen';
$string['bulkcourseintro'] = 'Legt mehrere Kurse gleichzeitig in derselben Kategorie an.';
$string['bulkcourse_entries_easyadmin'] = 'Kurse (ein Eintrag pro Zeile)';
$string['bulkcourse_entries_easyadmin_help'] = 'Format je Zeile: Vollständiger Name;Kurzbezeichnung, z. B.:
Mathematik Klasse 3a;MA3A
Deutsch Klasse 3a;DE3A
Zeilen mit bereits vergebener Kurzbezeichnung werden übersprungen, es entstehen also keine Dubletten.';
$string['bulkcourse_create_button'] = 'Kurse anlegen';
$string['bulkcourse_created_easyadmin'] = '{$a->created} Kurs(e) wurden angelegt, {$a->skipped} übersprungen (Kurzbezeichnung bereits vergeben), {$a->invalid} Zeile(n) im falschen Format ignoriert.';

// Einschreibung bei Kurserstellung.
$string['enrolheader_easyadmin'] = 'Einschreibung (optional)';
$string['teacheridentifier_easyadmin'] = 'Lehrkraft als Trainer/in einschreiben';
$string['teacheridentifier_easyadmin_help'] = 'Anmeldename oder E-Mail-Adresse einer bestehenden Person. Wird als "Trainer/in" (Lehrkraft-Rolle) in den Kurs eingeschrieben. Leer lassen, um niemanden einzuschreiben.';
$string['cohortenrol_easyadmin'] = 'Globale Gruppe als Teilnehmer/innen verknüpfen';
$string['cohortenrol_easyadmin_help'] = 'Verknüpft eine globale Gruppe (Kohorte) automatisch mit dem Kurs - alle aktuellen und künftigen Mitglieder werden dadurch fortlaufend als Teilnehmer/innen eingeschrieben, ohne dass Sie das manuell nachpflegen müssen.';
$string['cohortoption_none_easyadmin'] = 'Keine';
$string['bulkcourse_enrolnotice_easyadmin'] = 'Gilt für ALLE oben angelegten Kurse dieses Durchgangs.';
$string['error_teachernotfound_easyadmin'] = 'Es wurde kein Nutzerkonto mit diesem Anmeldenamen oder dieser E-Mail-Adresse gefunden.';

// Nutzer & Gruppen: Mini-Dashboard.
$string['usersdashboardintro'] = 'Wählen Sie einen Bereich aus.';
$string['subtile_createusers_desc'] = 'Einzeln oder als Liste';
$string['subtile_cohorts_desc'] = 'Einzeln oder als Liste';
$string['backtousers_easyadmin'] = 'Zurück zur Nutzer & Gruppen-Übersicht';

// Nutzer: mehrere auf einmal.
$string['bulkuserheader'] = 'Mehrere Nutzer auf einmal anlegen';
$string['bulkuserintro'] = 'Legt mehrere manuelle Nutzerkonten gleichzeitig an. Passwörter werden automatisch erzeugt und nach dem Anlegen einmalig angezeigt.';
$string['bulkuser_entries_easyadmin'] = 'Nutzer/innen (ein Eintrag pro Zeile)';
$string['bulkuser_entries_easyadmin_help'] = 'Format je Zeile: Vorname;Nachname;E-Mail;Anmeldename, z. B.:
Anna;Muster;anna.muster@schule.de;amuster
Ben;Beispiel;ben.beispiel@schule.de;bbeispiel
Zeilen mit bereits vergebenem Anmeldenamen oder ungültiger/bereits verwendeter E-Mail-Adresse werden übersprungen. Über diesen Weg werden keine Administrator-Konten vergeben - nutzen Sie dafür das Einzelformular oben.';
$string['bulkuser_create_button'] = 'Nutzer anlegen';
$string['bulkuser_created_easyadmin'] = '{$a->created} Nutzer wurden angelegt, {$a->skipped} übersprungen (Anmeldename/E-Mail bereits vergeben oder ungültig), {$a->invalid} Zeile(n) im falschen Format ignoriert.';
$string['bulkuser_passwordnotice'] = 'Wichtig: Notieren Sie sich die folgenden Passwörter jetzt - sie werden aus Sicherheitsgründen nirgends gespeichert und können später nicht erneut angezeigt werden. Alle Konten müssen das Passwort bei der ersten Anmeldung ändern.';

// Globale Gruppen: mehrere auf einmal.
$string['bulkcohortheader'] = 'Mehrere globale Gruppen auf einmal anlegen';
$string['bulkcohortintro'] = 'Legt mehrere globale Gruppen (Kohorten) gleichzeitig an.';
$string['bulkcohort_names_easyadmin'] = 'Namen (ein Eintrag pro Zeile)';
$string['bulkcohort_names_easyadmin_help'] = 'Ein Name pro Zeile, z. B.:
Klasse 3a
Klasse 3b
Bereits vorhandene globale Gruppen mit demselben Namen werden übersprungen, es entstehen also keine Dubletten.';
$string['bulkcohort_create_button'] = 'Globale Gruppen anlegen';
$string['bulkcohort_created_easyadmin'] = '{$a->created} globale Gruppe(n) wurden angelegt, {$a->skipped} bereits vorhandene übersprungen.';

// Kurse löschen.
$string['deleteheader'] = 'Kurse löschen';
$string['deleteintro'] = 'Achtung: Gelöschte Kurse können nicht wiederhergestellt werden. Erwägen Sie stattdessen "Kurse archivieren" weiter oben, falls Sie sich nicht ganz sicher sind, oder erstellen Sie vorher eine Sicherung unter "Kurse sichern".';
$string['delete_confirm_intro'] = 'Die folgenden Kurse werden UNWIDERRUFLICH gelöscht, inklusive aller Inhalte, Teilnehmer/innen-Daten und Bewertungen:';
$string['delete_acknowledge_easyadmin'] = 'Ich bin mir bewusst, dass diese Kurse endgültig und unwiderruflich gelöscht werden.';
$string['delete_confirm_button'] = 'Endgültig löschen';
$string['deleted_success_easyadmin'] = '{$a} Kurs(e) wurden endgültig gelöscht.';
$string['error_deletenotacknowledged'] = 'Bitte das Häkchen "Ich bin mir bewusst..." setzen, um die Löschung zu bestätigen.';

// Kurse: Mini-Dashboard.
$string['coursesdashboardintro'] = 'Wählen Sie einen Bereich aus.';
$string['subtile_categories_desc'] = 'Einzeln oder als Liste';
$string['subtile_createcourses_desc'] = 'Einzeln oder als Liste';
$string['subtile_archive_desc'] = 'Schuljahreswechsel';
$string['subtile_backup_desc'] = 'Zum Herunterladen';
$string['subtile_delete_desc'] = 'Endgültig entfernen';
$string['backtocourses_easyadmin'] = 'Zurück zur Kurse-Übersicht';

// Kurse verschieben.
$string['moveheader'] = 'Kurse verschieben';
$string['moveintro'] = 'Verschiebt ausgewählte Kurse in eine andere Kategorie.';
$string['subtile_move_desc'] = 'In andere Kategorie';
$string['move_targetcategory_easyadmin'] = 'Zielkategorie';
$string['move_confirm_intro'] = 'Die folgenden Kurse werden in die Kategorie "{$a}" verschoben:';
$string['move_confirm_button'] = 'Jetzt verschieben';
$string['moved_success_easyadmin'] = '{$a} Kurs(e) wurden verschoben.';
$string['error_notargetcategory'] = 'Bitte eine gültige Zielkategorie auswählen.';

// Kurse: Archivieren.
$string['archiveheader'] = 'Kurse archivieren';
$string['archiveintro'] = 'Blendet ausgewählte Kurse aus und verschiebt sie in eine Archiv-Kategorie. Ideal zum Schuljahresende.';
$string['archive_choosecategory_button'] = 'Kurse dieser Kategorie anzeigen';
$string['selectall_easyadmin'] = 'Alle auswählen/abwählen';
$string['archive_nocategories'] = 'Es sind noch keine Kurskategorien vorhanden.';
$string['archive_nocoursesincategory'] = 'In dieser Kategorie sind aktuell keine Kurse vorhanden.';
$string['archive_alreadyhidden'] = '(bereits ausgeblendet)';
$string['archive_targetcategoryname_easyadmin'] = 'Ziel-Kategorie für das Archiv';
$string['archive_defaultcategoryname'] = 'Archiv';
$string['archive_continue_button'] = 'Weiter zur Bestätigung';
$string['archive_cancel_button'] = 'Abbrechen';
$string['archive_confirm_intro'] = 'Die folgenden Kurse werden ausgeblendet und in die Archiv-Kategorie verschoben. Diese Aktion betrifft mehrere Kurse gleichzeitig - bitte die Liste sorgfältig prüfen:';
$string['archive_confirm_button'] = 'Ja, jetzt archivieren';
$string['archived_success'] = '{$a} Kurs(e) wurden erfolgreich archiviert.';
$string['error_nocoursesselected'] = 'Es wurde kein Kurs ausgewählt.';

// Kurse: Sicherung zum Herunterladen.
$string['backupheader'] = 'Kurse sichern';
$string['backupintro'] = 'Erstellt eine vollständige Sicherung (.mbz) einzelner Kurse zum direkten Herunterladen - im Unterschied zur automatischen Sicherung landet diese Datei nicht im gemeinsamen Sammelbereich, sondern kann gezielt für genau diesen einen Kurs wiederhergestellt werden, ganz ohne einen kompletten Server-Restore.';
$string['backup_confirm_intro'] = 'Für die folgenden Kurse wird jetzt eine vollständige Sicherung erstellt. Je nach Kursgröße kann dies einige Minuten dauern:';
$string['backup_confirm_button'] = 'Jetzt sichern';
$string['backup_done_intro'] = 'Die Sicherung ist fertig. Zum Herunterladen bitte auf die jeweilige Datei klicken:';
$string['backup_downloadlink'] = 'Sicherung herunterladen: {$a}';
$string['backup_downloadall_button'] = 'Alle als ZIP herunterladen';
$string['backup_downloadlink_notice'] = 'Hinweis: Die Sicherungsdateien liegen in einem eigenen, geschützten Dateibereich dieses Plugins und sind nur für Nutzer/innen mit Zugriff auf diese Kachel abrufbar.';
$string['backup_retention_notice'] = 'Sicherungsdateien werden automatisch nach 20 Tagen gelöscht (nächtliche Aufräum-Aufgabe).';
$string['task_cleanup_course_backups'] = 'Alte Kurs-Sicherungen löschen (Kurse-Kachel)';
$string['logretentionheader'] = 'Log-Aufbewahrung';
$string['loglifetime_easyadmin'] = 'Logdaten aufbewahren für';
$string['loglifetime_easyadmin_help'] = 'Legt fest, wie lange Moodle Protokolldaten (wer hat wann was gemacht) speichert, bevor sie automatisch gelöscht werden. Aus Datenschutzgründen bietet diese Auswahl bewusst keine "Nie löschen"-Option an. Werte unter 30 Tagen werden nicht empfohlen, da dann z. B. Statistiken nicht mehr zuverlässig funktionieren.';
$string['logretention_never'] = 'Nie löschen';
$string['logretention_never_current'] = 'Aktuell: Nie löschen (aus Datenschutzgründen bitte ändern)';
$string['logretention_days'] = '{$a} Tage';
$string['logretention_current'] = 'Aktueller Wert: {$a} Tage';
$string['usercount_easyadmin'] = 'Nutzer/innen insgesamt: {$a}';
$string['coursecount_easyadmin'] = 'Kurse insgesamt: {$a}';
$string['lockoutthreshold_easyadmin'] = 'Sperre nach so vielen fehlgeschlagenen Anmeldeversuchen';
$string['lockoutthreshold_easyadmin_help'] = 'Nach dieser Anzahl fehlgeschlagener Anmeldeversuche wird das betroffene Konto vorübergehend gesperrt, um es vor Erraten des Passworts zu schützen.';
$string['displayloginfailures_easyadmin'] = 'Nutzer/innen über fehlgeschlagene Anmeldeversuche informieren';
$string['displayloginfailures_easyadmin_help'] = 'Zeigt Nutzer/innen nach der Anmeldung, wie oft seit dem letzten Login fehlgeschlagene Anmeldeversuche mit ihrem Konto unternommen wurden.';
$string['allowframembedding_easyadmin'] = 'Einbettung dieser Website in fremden Webseiten erlauben (Rahmen/iFrames)';
$string['allowframembedding_easyadmin_help'] = 'Achtung: Diese Einstellung sollte in der Regel deaktiviert bleiben, da sie sogenannte Clickjacking-Angriffe erleichtern kann, bei denen Ihre Seite unsichtbar in eine fremde Webseite eingebettet wird.';
$string['sitepolicy_easyadmin'] = 'Link zur Datenschutzerklärung';
$string['sitepolicy_easyadmin_help'] = 'Adresse (URL) Ihrer Datenschutzerklärung. Nutzer/innen müssen dieser bei der ersten Anmeldung zustimmen, falls hier eine Adresse hinterlegt ist. Hinweis: Dies ist eine einfache Verlinkung. Für vollständige Dokumente mit Versionierung und Zustimmungspflicht nutzen Sie bitte die Moodle-Richtlinienverwaltung (Button oben).';
$string['policytool_heading'] = 'Ausführliche Richtlinien (optional)';
$string['policytool_hint'] = 'Moodle bietet eine eigene Verwaltung für vollständige Dokumente wie Datenschutzerklärung oder Nutzungsordnung, inklusive Versionierung und Zustimmungspflicht beim Login. Achtung: Dafür wird zusätzlich zu den Rechten dieses Dashboards die Berechtigung "tool/policy:managedocs" benötigt - falls der Button unten "Kein Zugriff" meldet, wenden Sie sich an die technische Betreuung, damit diese Berechtigung ergänzt wird.';
$string['policytool_linktext'] = 'Zur Moodle-Richtlinienverwaltung';
$string['sitepolicyhandler_usetool'] = 'Erweiterte Richtlinienverwaltung aktivieren';
$string['sitepolicyhandler_usetool_help'] = 'Muss aktiviert sein, damit der Button "Zur Moodle-Richtlinienverwaltung" unten funktioniert - sonst erscheint dort ein "Bereichsfehler". Solange dieses Häkchen nicht gesetzt ist, verwendet Moodle stattdessen den einfachen Link im Feld "Link zur Datenschutzerklärung" weiter unten.';
$string['error_minpasswordlength_range'] = 'Bitte einen Wert zwischen 4 und 64 angeben.';
$string['error_lockoutthreshold_range'] = 'Bitte einen Wert zwischen 1 und 100 angeben.';
$string['frontpage_easyadmin'] = 'Ansicht vor der Anmeldung';
$string['frontpage_easyadmin_help'] = 'Was nicht angemeldete Besucher/innen auf der Startseite sehen. Hinweis: Falls in der Website-Administration bisher mehrere Ansichten kombiniert waren, wird beim Speichern hier nur die gewählte einzelne Option verwendet.';
$string['frontpageloggedin_easyadmin'] = 'Ansicht nach der Anmeldung';
$string['frontpageloggedin_easyadmin_help'] = 'Was angemeldete Nutzer/innen auf der Startseite sehen. Hinweis: Falls in der Website-Administration bisher mehrere Ansichten kombiniert waren, wird beim Speichern hier nur die gewählte einzelne Option verwendet.';
$string['frontpageoption_none'] = 'Keine Anzeige';
$string['frontpageoption_news'] = 'Aktuelle Nachrichten';
$string['frontpageoption_categories'] = 'Kategorienamen';
$string['frontpageoption_combo'] = 'Kombinierte Kategorien-/Kursliste';
$string['frontpageoption_allcourses'] = 'Liste aller Kurse';
$string['frontpageoption_enrolledcourses'] = 'Liste der eingeschriebenen Kurse';
$string['frontpageoption_coursesearch'] = 'Kurssuche';
$string['error_required'] = 'Dieses Feld darf nicht leer sein.';
$string['error_invalidurl'] = 'Bitte eine gültige, auf diese Website bezogene Adresse angeben.';
