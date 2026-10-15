# Changelog

## 1.5.0

- **Anmeldeseite**: Deckkraft des Anmeldefensters einstellbar (Standard
  deckend), Gast-Anmeldung wahlweise vor die externen Anbieter oder ganz
  nach oben setzbar, Hinweistext kann die Überschrift „Willkommen“ im
  Anmeldefenster ersetzen

## 1.4.0

- **Design, Anmeldeseite (Moodle 5.2+)**: Info-Kachel ausblendbar,
  Hintergrundbild über die gesamte Seite, bis zu 10 Bilder im zufälligen
  Start und einstellbarem Bildwechsel, Anmeldefenster links, mittig oder
  rechts (jeweils mit Kontexthilfe). Ersetzt die frühere Einstellung
  „Standard-Bild ausblenden/ersetzen“

## 1.3.0

- **Neue Kachel „Blöcke"**: zusätzliche Block-Bereiche im Inhaltsbereich
  oben und unten, einzeln für die Startseite und die Kurs-Hauptseite
  aktivierbar (jeweils mit Kontexthilfe). Neue Capability
  local/easyadmin:manageblocks
- **Design**: Die Einstellung „Maximale Seitenbreite“ hat jetzt einen eigenen
  Abschnitt „Seitenbreite“ und passt auch Seitenkopf und Fußleiste an
- Kachel „Nutzer & Gruppen" heißt jetzt „Nutzer & Globale Gruppen"

## 1.2.0

- **Schul-Kacheln**: Kachelgröße (klein, mittel, groß) und Ausrichtung
  (links, mittig, rechts) einstellbar, jeweils mit Kontexthilfe
- Behoben: Die automatische Löschung inaktiver Nutzerkonten (geplante
  Aufgabe) lädt jetzt die Plugin-Bibliothek lib.php selbst nach

## 1.0.0

Erste Marktplatz-Einreichung. Zusammenfassung der Entwicklung bis zu diesem
Stand:

- Dashboard-Rahmen mit Kachel-Übersicht und automatischem Menüpunkt in der
  Kopfzeilen-Navigation
- **Design**: Logo, Hintergrundbild/-farbe, Textfarbe/-größe, Begrüßungs-
  Popup, Anmeldeseiten-Bild
- **Startseite**: Name, Kurzbezeichnung, Beschreibung, Anzeigeoptionen,
  automatische Anzeige des Kursbeschreibung-Blocks
- **Nutzer & Gruppen**: Nutzerkonten anlegen (einzeln und als Liste),
  optionale, streng abgesicherte Administrator-Vergabe, globale Gruppen
  (Kohorten) inklusive Massen-Erstellung
- **Kurse**: Kategorien, Kurserstellung (einzeln/als Liste, inkl. optionaler
  Lehrkraft-/Gruppen-Einschreibung), Verschieben, Archivieren (Schuljahres-
  wechsel), Sichern (herunterladbare Einzelkurs-Sicherungen mit
  automatischer Löschung nach 20 Tagen), Löschen (mit Pflicht-Bestätigung)
- **Kommunikation**: Mitteilungssystem-Schalter, optionale Anbindung an
  lokale Mail-Plugins (`local_mail`, `local_emailclient`), sofern installiert
- **Sicherheit und Datenschutz**: vollständige Passwortrichtlinie,
  Anmeldesperre, Rahmen-Einbettung, Datenschutzerklärung, Anbindung an
  Moodles Richtlinienverwaltung
- **Wartung & Berichte**: Debug-Modus mit Warnhinweis, Caches leeren,
  einfache Zugriffszahlen, einstellbare Log-Aufbewahrung
- **Schul-Kacheln**: vollständig integrierte Verwaltung von bis zu 8
  Startseiten-Kacheln (vormals separates Plugin `local_schooltiles`) über
  ein geführtes Formular statt 64 einzelner Website-Administrations-
  Rohfelder; Ziel wählbar als Kurskategorie (echtes Dropdown) oder eigene
  Adresse, Sichtbarkeit optional auf eine globale Gruppe beschränkt

Durchgängige Sicherheitsprinzipien: granulare Capabilities je Bereich,
ausschließliche Nutzung offizieller Moodle-Core-/Plugin-APIs, mehrstufige
Bestätigungsabläufe vor kritischen/destruktiven Aktionen, serverseitige
Zusatzprüfungen bei besonders sensiblen Vorgängen (Administrator-Vergabe,
Kurslöschung).
