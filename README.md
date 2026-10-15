# Einfache Schul-Administration (local_easyadmin)

Ein vereinfachtes Verwaltungs-Dashboard für Moodle, gedacht für Schul-Admins
ohne tiefe technische Vorkenntnisse (insbesondere an Grundschulen). Bündelt
die am häufigsten benötigten Verwaltungsaufgaben in einer kachelbasierten
Oberfläche mit großen, verständlichen Bedienelementen und Fließtext-
Erklärungen statt technischer Fachbegriffe - als Ergänzung zur regulären
Website-Administration, nicht als Ersatz dafür.

## Funktionsübersicht

| Kachel | Funktionen |
| --- | --- |
| **Design** | Logo, Hintergrundbild/-farbe, Textfarbe/-größe, Begrüßungs-Popup, Anmeldeseiten-Bild |
| **Startseite** | Name, Kurzbezeichnung, Beschreibung, Anzeigeoptionen, Kursbeschreibung-Block |
| **Nutzer & Globale Gruppen** | Nutzerkonten anlegen (einzeln/mehrere), globale Gruppen (Kohorten) |
| **Kurse** | Kategorien, Kurse anlegen/verschieben/archivieren/sichern/löschen |
| **Kommunikation** | Mitteilungssystem, Anbindung an lokale Mail-Plugins (optional) |
| **Sicherheit und Datenschutz** | Passwortrichtlinie, Anmeldesperre, Datenschutzerklärung |
| **Wartung & Berichte** | Debug-Modus, Caches leeren, Zugriffszahlen, Log-Aufbewahrung |
| **Blöcke** | Zusätzliche Block-Bereiche im Inhaltsbereich oben/unten der Startseite und der Kurs-Hauptseite |
| **Schul-Kacheln** | Bis zu 8 konfigurierbare Startseiten-Kacheln (Kategorie/Adresse, Bild/Emoji, Farben, Sichtbarkeit per globaler Gruppe, einstellbare Kachelgröße und Ausrichtung) |

## Voraussetzungen

- Moodle 5.1 oder neuer

## Installation

Wie jedes Moodle-Plugin: ZIP-Datei über Website-Administration → Plugins →
Plugins installieren hochladen, oder den Ordner nach `local/easyadmin`
entpacken.

Nach der Installation erscheint automatisch ein Menüpunkt "Schul-
Administration" in der Kopfzeilen-Navigation für berechtigte Nutzer/innen
(Capability `local/easyadmin:view`).

## Berechtigungen

Jede Kachel besitzt eine eigene, granulare Capability
(`local/easyadmin:managedesign`, `local/easyadmin:manageusers`, ...), die
Website-Administrator/innen gezielt an eine eigene Schul-Admin-Rolle
vergeben können, ohne volle Website-Administratorrechte einzuräumen.

**Ausnahme:** Die Option "Zum Administrator machen" beim Anlegen eines
Nutzerkontos erscheint ausschließlich für Personen, die bereits selbst
echte Website-Administrator/innen sind - dieses Recht kann über keine
Kombination von Capabilities dieses Plugins erlangt werden.

## Datenschutz

Das Plugin speichert selbst keine dauerhaften Nutzerprofil-Daten, mit einer
Ausnahme, die automatisch bereinigt wird:

- Kurs-Sicherungsdateien (können Teilnehmer/innen-Daten enthalten) werden
  nach 20 Tagen automatisch gelöscht (geplante Aufgabe).

Protokolldaten (Logs) unterliegen zusätzlich einer einstellbaren
Aufbewahrungsfrist (mindestens 30 Tage, keine Option für unbegrenzte
Aufbewahrung).

## Support

Moodle in Niedersachsen e. V.
