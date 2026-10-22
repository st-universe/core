# Starmap Realtime

PHP erzeugt Zugriffstokens, speichert Sensor-Coverage in Redis und veröffentlicht
Bewegungen. Ein separater Node-Prozess liefert die Ereignisse per WebSocket aus

## Konfiguration

Einstellungen unter `realtime` in `config/config.json`:

| Feld | Bedeutung |
| --- | --- |
| `host`, `port` | Bind-Adresse des Node-Dienstes |
| `path` | WebSocket-Endpunkt |
| `webSocketUrl` | Vom Browser erreichbare WebSocket-URL |
| `redisNamespace` | Präfix für Bewegungsstream und Coverage; Standard `stu` |
| `coverageReloadMs` | Coverage-Aktualisierung; Standard 60000 ms |

Redis-Verbindung über `cache.redis_socket` beziehungsweise
`cache.redis_host` und `cache.redis_port`. PHP und Node müssen dieselbe
Redis-Instanz und denselben Namespace verwenden

## Betrieb

Start über `npm run realtime:starmap`; Prozessüberwachung separat.
Ein Reverse-Proxy muss WebSocket-Upgrades unterstützen, unter HTTPS ist WSS nötig

PHP und Node benötigen denselben zufälligen Signaturschlüssel mit mindestens
32 Zeichen. Ein sicherer vorhandener `game.map.encryptionKey` kann weiterverwendet
werden. Die öffentliche Beispielbelegung und der Datenbankname werden abgewiesen;
ein fehlender Schlüssel hat keinen Ersatzwert

Für einen neuen Schlüssel beispielsweise `openssl rand -hex 32` verwenden und den
Wert in `game.map.encryptionKey` hinterlegen oder als `STU_REALTIME_SECRET` an beide
Prozesse übergeben. Die Umgebungsvariable hat bei PHP und Node Vorrang; ein leerer
Wert wird als Fehler abgewiesen. Bei einem Wechsel von `game.map.encryptionKey`
müssen außerdem bestehende mit diesem Schlüssel verschlüsselte Kartendaten
berücksichtigt werden. Ein eigener `STU_REALTIME_SECRET` erlaubt die Umstellung
der Realtime-Signaturen unabhängig von der Kartenverschlüsselung

Node liest `STU_CONFIG_PATH` oder `config/config.json`, ersatzweise
`config/config.dist.json`. PHP verwendet seine zusammengeführten
Konfigurationsdateien. Beide Dienste müssen denselben wirksamen Schlüssel erhalten.
Ohne gültigen Schlüssel startet der Node-Dienst nicht und PHP stellt keine Tokens aus

Zum Wechsel PHP und Node mit dem neuen Wert neu starten und die Karte neu öffnen.
Alte Tokens sind anschließend ungültig. Getrennte Installationen benötigen
getrennte Namespaces, Signaturschlüssel und bei gemeinsamem Host unterschiedliche
Bind-Adressen beziehungsweise Ports

Nach Namespace-Änderungen Node neu starten und die Karte neu öffnen.
Kein Redis-Neustart oder Leeren der Daten nötig. Der Namespace trennt nur
Kartendaten, nicht sämtliche Core-Caches

Optionale Module: [Extension-Deployment](extensions-deployment.md)
