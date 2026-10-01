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

Token-Schlüssel aus `game.map.encryptionKey`; ein Node-Override über
`STU_REALTIME_SECRET` muss zum PHP-Signaturschlüssel passen.
Getrennte Installationen benötigen getrennte Namespaces, Signaturschlüssel
und bei gemeinsamem Host unterschiedliche Bind-Adressen beziehungsweise Ports

Nach Namespace-Änderungen Node neu starten und die Karte neu öffnen.
Kein Redis-Neustart oder Leeren der Daten nötig. Der Namespace trennt nur
Kartendaten, nicht sämtliche Core-Caches

Optionale Module: [Extension-Deployment](extensions-deployment.md)
