STU3
====

Lokales Setup
-------------

Benoetigte Software: Linux (geht u.U. auch unter macOS) docker, php 7.3,
composer

- Repository forken
- Source Code auschecken und folgende Befehle ausfuehren
- config.dist.json nach config.json kopieren und anpassen
- make init
- make dev-create-db
- make generators
- make dev-serve

Danach sollte die Software via http://localhost:1337 erreichbar sein. Ggf kann
es sein, dass der Hostname (stu-db) nicht aufgelöst werden kann. In diesem
Fall muss für die im `docker-compose.yml` File hinterlegte IP ein Eintrag in
der Hosts-Datei des Betriebssystems gemacht werden.

API
---

Alle Namesspaces der API, mit Ausnahme von `common`, erwarten eine Authentifizierung mittels des über `common/login` zu bekommenden Tokens.
Dieser Token muss mittels `Authorization`-Header und dem Inhalt `Bearer <TOKEN>` bei jedem Request übergeben werden.

**Common - News**

`GET /api/v1/common/news`

Response
```json
{
    "statusCode": 200,
    "data": [{
        "headline":"Some headline",
        "text":"Some text",
        "date": 1565430813,
        "links":[
            "https://example.com"
        ]
    }]
}
```

**Common - Factions**

`GET /api/v1/common/faction`

Response
```json
{
    "statusCode": 200,
    "data": [
        {
            "id": 1,
            "name": "Some faction name",
            "description": "Faction description",
            "player_limit": 20,
            "player_amount": 5
        }
    ]
}
```

**Common - Login**

`POST /api/v1/common/login`

Request
```json
{
    "username": "john",
    "password": "doe"
}
```

Response
```json
{
    "statusCode": 200,
    "data": {
        "token": "sample-token"
    }
}
```

**Colony - Retrieve list**

Returns a list of colony ids.

`GET /api/v1/colony`

Response
```json
{
  "statusCode": 200,
  "data": [
    123,
    456
  ]
}
```

**Colony - Retrieve single colony**

Retrieve the basic data for the colony with the supplied id.

`GET /api/v1/colony/<colonyId>`

Response
```json
{
  "statusCode": 200,
  "data": {
    "id": 123,
    "name": "A fine colony"
  }
}
```

**Player - Retrieve current player**

Retrieve the details of the current player.

`GET /api/v1/player`

Response
```json
{
    "statusCode": 200,
    "data": {
        "id": 105,
        "factionId": 1,
        "name": "Siedler 105",
        "allianceId": 7,
        "avatarPath": "/assets/rassen/1kn.png"
    }
}
```
