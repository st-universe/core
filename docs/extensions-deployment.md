# Extension-Deployment

Konfiguration unter `extensions.<id>` in der nicht versionierten
`config/config.json`. Module werden unabhängig von Composer geladen

## Konfiguration

| Feld | Bedeutung |
| --- | --- |
| `enabled` | Aktiviert Download und Registrierung |
| `path` | Lokaler Checkout, alternativ zu `deployment` |
| `deployment.repository` | Repository mit gültigem `module.php` |
| `deployment.branch` | Zu verfolgender Remote-Branch |
| `deployment.intervalSeconds` | Prüfintervall: Standard 300, Minimum 60 Sekunden |
| `deployment.restartCommand` | Optionales Argument-Array für den Dienstneustart |

## Verhalten

`sync.sh` prüft aktivierte Module auch ohne Core-Änderung. Voraussetzung ist
nichtinteraktiver Repository-Zugriff. Bei Downloadfehlern bleibt die installierte
Version erhalten; ohne vorhandene Version wird das Modul übersprungen

Module liegen unter `var/extensions/<id>/current` außerhalb des Webroots.
Nur deklarierte Browserassets werden nach `src/Public/static/extensions` kopiert

Updates führen Cache-Leerung, Datenbankmigrationen und Asset-Veröffentlichung aus.
Aktivieren kann daher bereits beim nächsten Sync das Datenbankschema ändern.
`restartCommand` läuft anschließend; ohne diesen Eintrag Dienste separat
neu starten. Deaktivieren stoppt laufende Dienste nicht
