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

Alle regulären Responses sind nach dem gleichen Prinzip aufgebaut:
```metadata json
{
    "data": ...
}
```

Ist ein Fehler vorgefallen, sieht die Response wie folgt aus:
```metadata json
{
    "errorCode": ?int,
    "error": "string"
}
```
Die StatusCodes können dem `ErrorCodeEnum` entnommen werden

**Common - News**

`GET /api/v1/common/news`

Response
```metadata json

[
  {
    "headline": strinh,
    "text": string,
    "date": int,
    "links": string[]
  }
]

```

**Common - Factions**

`GET /api/v1/common/faction`

Response
```metadata json
[
    {
        "id": int,
        "name": string,
        "description": string,
        "player_limit": int,
        "player_amount": int
    }
]
```

**Common - Login**

`POST /api/v1/common/login`

Request
```metadata json
{
    "username": string
    "password": string
}
```

Response
```metadata json
{
    "token": string
}
```

**Common - Register new player**

`POST /api/v1/common/player/new`

Request
```metadata json
{
    "loginName": string,
    "emailAddress": string,
    "factionId": int
}
```

Response
```metadata json
true
```

**Colony - Retrieve list**

Returns a list of colony ids.

`GET /api/v1/colony`

Response
```metadata json
int[]
```

**Colony - Retrieve single colony**

Retrieve the basic data for the colony with the supplied id.

`GET /api/v1/colony/<colonyId>`

Response
```metadata json
{
    "id": int,
    "name": string
}
```

**Player - Retrieve current player**

Retrieve the details of the current player.

`GET /api/v1/player`

Response
```metadata json
{
    "id": int,
    "faction_id": int,
    "name": string,
    "alliance_id": ?int,
    "avatar_path": string
}
```

**Player - Retrieve new private message count**

Retrieve the amount of new private messages for all system folders.

* 1 = General/Player-to-player messages
* 2 = Ship related messages
* 3 = Colony related messages
* 4 = Trade related messages

`GET /api/v1/player/newpms`

Response
```metadata json
{
    "folder_special_id": int,
    "new_pm_amount": int
}
```

