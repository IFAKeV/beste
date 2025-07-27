# Funktionsübersicht des Repositories

Das Projekt stellt ein webbasiertes Adressbuch der „IFAK Familie“ bereit. HTML/PHP erzeugen die Oberfläche, während JavaScript die Anzeige und Filterung der Daten übernimmt.

##Wichtige Dateien und Aufgaben

| Datei/Verzeichnis         | Zweck                                                                                                                                  |
| ------------------------- | -------------------------------------------------------------------------------------------------------------------------------------- |
| `index.php`               | Hauptseite des Adressbuchs inkl. Suchfeld, Filtermenü und Exportlinks. Dient gleichzeitig als Einstiegspunkt für PDF‑Exportfunktionen. |
| `script.js`               | Lädt Daten (JSON), filtert und rendert Karten für Personen, Einrichtungen und Standorte. Zeigt Details in einem Modal an.              |
| `styles.css`              | Gestaltet Layout und Modal-Fenster.                                                                                                    |
| `init.php`                | Stellt die Verbindung zur SQLite‑Datenbank her und liest Stammdaten (Sprachen, Einrichtungen, Standorte) ein.                          |
| `phonelist.php`           | Erstellt eine Telefonliste als PDF mit FPDF.                                                                                           |
| `list.php`                | Erstellt eine allgemeine Mitarbeitendenliste als PDF mit FPDF.                                                                         |
| `fpdf.php` und `font/`    | Eingebundene PDF-Bibliothek und Schriftdateien.                                                                                        |
| `README.md`, `roadmap.md` | Sehr knappes Projektintro und offene Punkte.                                                                                           |
| `.gitignore`              | Schließt Datenbankdateien und lokale Artefakte aus dem Repository aus.                                                                 |


## Abläufe im Detail

1.  Startseite (index.php):

        Prüft zunächst, ob bestimmte Exportparameter (phonelist, list etc.) aufgerufen wurden und lädt dann init.php und eine PHP‑Datei für den Export

.

Lädt ansonsten die eigentliche Weboberfläche mit Suchfeld, Filter (Personen, Einrichtungen, Standorte) und bindet script.js ein

.

Im Footer werden Links für PDF‑Exporte angeboten

    .

JavaScript (script.js):

    Lädt beim Start die Datei ifak.json mit den Daten für Personen, Einrichtungen und Standorte

.

Eine Funktion filterAndRenderData durchsucht Name, E‑Mail und Telefonnummern und wendet Filterkriterien an

.

Gefundene Einträge werden als Karte („card“) ausgegeben und bei Klick im Modal dargestellt. Dabei werden Telefonnummern formatiert und Informationen zu Einrichtungen bzw. Sprachen ergänzt

.

Am Ende werden Event Listener registriert, um Sucheingaben und Filteränderungen abzufangen

    .

Datenbank-Initialisierung (init.php):

    Öffnet die SQLite‑Datenbank ifak.db und lädt Sprachen, Einrichtungen und Standorte in Arrays

.

Berechnet auch die Anzahl der vorhandenen Sprachen, vermutlich für Statistikzwecke

    .

PDF-Export (phonelist.php, list.php):

    Beide Dateien setzen auf die FPDF‑Bibliothek (Version 1.86)

.

phonelist.php formatiert Telefonnummern einheitlich und erzeugt einen detailreichen Telefonlisten-PDF

.

list.php erstellt eine allgemeine Mitarbeitendenliste und listet pro Person verknüpfte Einrichtungen sowie Kontaktinfos auf

    .

Weitere Inhalte:

    Im Repository befinden sich nur Platzhalter für die Daten (ifak.db/ifak.json sind in .gitignore eingetragen und fehlen)

.

Die Dokumentation fällt minimal aus; README.md enthält lediglich einen Satz
und roadmap.md erwähnt noch ausstehende Punkte zur Ausgabe von Fachbereichen aus der Datenbank

.

index.php versucht eine (nicht vorhandene) functions.php einzubinden, was zu Ladefehlern führen kann

        .

# Zusammenfassung

Das Projekt bildet ein einfaches Adressbuch der IFAK‑Organisation ab, das Daten aus einer SQLite‑Datenbank (bzw. einem exportierten JSON) liest und sie im Browser durchsuch- und filterbar darstellt. Zusätzlich lassen sich Telefon- und Mitarbeitendenlisten per PDF exportieren. Verbesserungswürdig sind die Dokumentation, ein fehlendes functions.php sowie kleinere Code-Aufräumarbeiten (z. B. .gitignore‑Zeilenumbruch). Overall bietet der Code eine solide Grundfunktionalität für ein organisationsinternes Adressbuch, aber einige offene Punkte sind noch zu bearbeiten.
