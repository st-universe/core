# Starmap Realtime

Der User-Starmap-WebSocket läuft als eigener Node-Prozess. PHP bleibt autoritativ: Beim Öffnen der Live-Sensorkontakte erzeugt PHP ein kurzlebiges Token, schreibt die Sensor-Coverage des Users nach Redis und veröffentlicht Bewegungen als Redis-Stream.

## Installation

```bash
cd /var/www/stu/core
npm install
STU_REALTIME_SECRET='dein game.map.encryptionKey wert' pm2 start npm --name stu-starmap-realtime -- run realtime:starmap
pm2 save
```

Wenn `game.map.encryptionKey` in `config/config.json` gesetzt ist, kann `STU_REALTIME_SECRET` auch weggelassen werden.

## Lokal ohne Nginx

Wenn du lokal den PHP-Built-in-Server nutzt, reicht nach der einmaligen Installation:

```bash
npm install
composer dev:serve-node
```

`composer dev:serve-node` prüft Redis, startet bei Bedarf einen lokalen Redis ohne Persistenz, startet den Node-WebSocket-Server und danach den PHP-Built-in-Server auf `localhost:1337`.
`composer dev:serve` bleibt der reine PHP-Built-in-Server ohne Realtime-Node-Prozess.

Die lokale WebSocket-URL kommt aus `config/config.json`:

```json
"realtime": {
  "host": "127.0.0.1",
  "port": 8787,
  "path": "/realtime/starmap",
  "webSocketUrl": "ws://127.0.0.1:8787/realtime/starmap",
  "coverageReloadMs": 60000
}
```

Für einen einzelnen Start kann `STU_REALTIME_WEBSOCKET_URL` die Config überschreiben.

## Nginx

In den `nginx`-Serverblock:

```nginx
location /realtime/starmap {
    proxy_pass http://127.0.0.1:8787/realtime/starmap;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "upgrade";
    proxy_set_header Host $host;
    proxy_read_timeout 75s;
}
```

Danach:

```bash
sudo nginx -t
sudo systemctl reload nginx
```

## Checks

```bash
pm2 logs stu-starmap-realtime
redis-cli XINFO STREAM stu:realtime:starmap:spacecraft
```
