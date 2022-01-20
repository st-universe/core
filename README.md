Star Trek Universe
==================

Lokales Setup
-------------

Benoetigte Software: Linux (geht u.U. auch unter macOS) docker, php 7.4,
composer, git.

- Repository forken und das Source-Code auschecken
- config.dist.json nach config.json kopieren und anpassen
- make init
- vendor/bin/doctrine orm:generate-proxies
- make dev-create-db
- php src/admin/scripts/reset.php
- make dev-serve

Danach sollte die Software via http://localhost:1337 erreichbar sein, ggf. kann
es sein, dass der Hostname (stu-db) nicht aufgelöst werden kann. In diesem
Fall muss für die im `docker-compose.yml` File hinterlegte IP ein Eintrag in
der Hosts-Datei des Betriebssystems gemacht werden.

Server-Deployment
-----------------

Code:

- Repository auschecken
- make init-production
- make dirs
- rsync -rv --delete-after --exclude=assets --links --exclude=config.json --exclude=src/Public/admin/manage --exclude=src/Public/avatare/* src bin vendor Makefile cli-config.php config.dist.json /path/to/stuniverse-source/
- cd /path/to/stuniverse-source && make clearCache && make migrateDatabase

Assets:

- Repository auschecken
- php generator/building_generator/gen.php
- php generator/field_generator/generator.php
- rsync -rv --delete-after --exclude=dist --exclude=".git" . /path/to/stuniverse-source/assets/

DB-Dump aus Backups einspielen
------------------------------

- aktuelles Schema umbenennen, z.B. in 'stuDamaged'
- neue Datenbank mit Original-Name erstellen, z.B. 'stu'
- auf Kommandozeile in den postgres user einloggen, mittels 'sudo su - postgres'
- in Backup-Folder wechseln, z.B: core/dist/db/backup
- Backup einspielen mittels 'pg_restore -d stu -U postgres -C dd-MM-yyyy.dump'

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
    "error": {
        "errorCode": ?int,
        "error": "string"
    }
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
    "factionId": int,
    "token": string
}
```

Response
```metadata json
true
```

**Colony - Retrieve list**

Retrieve all vital info forthe colony list.

`GET /api/v1/colony`

Response
```metadata json
{
    "id": int,
    "name": string,
    "location": {
        "planetName": string,
        "systemName": string,
        "systemType": int,
        "systemCx": int,
        "systemCy": int,
        "sx": int,
        "sy": int
    },
    "population": {
        "working": int,
        "workless": int,
        "freeHousing": int,
        "maximumHousing": int 
    },
    "energy": {
        "currentAmount": int,
        "maximumAmount": int,
        "production": int
    },
    "storage": {
        "currentAmount": int,
        "maximumAmount": int,
        "commodityConsumption": [
            {
                "commodityId": int,
                "production": int,
                "turnsLeft": int
            }
        ]
    }
}
```

**Colony - Retrieve single colony**

Retrieve the basic data for the colony with the supplied id.

`GET /api/v1/colony/<colonyId>`

Response
```metadata json
{
    "colonyId": int,
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

**Player - Retrieve the current research state**

`GET /api/v1/player/research/current`

Response without research
```metadata json
{
    null
}
```

Response wit research
```metadata json
{
    "tech": [
        "id": int,
        "name": string,
        "points": int
    ],
    "pointsLeft": int
}
```

**Player - Retrieve research list**

Retrieves all avaiable and all finished researches.

`GET /api/v1/player/research`

Response
```metadata json
"available": [
    {
        "researchId": int,
        "name": string,
        "description": string,
        "points": int,
        "commodity": {
            "commodityId": int,
            "name": string
        }
    }
],
"finished": [
    {
        "researchId": int,
        "name": string,
        "finishDate": int   
    }
]
```

**Player - Cancel current research**

`POST /api/v1/player/research/cancel`

Response

```metadata json
true
```

**Player - Start new research**

Starts a new research and cancels the current one (if available).

`POST /api/v1/player/research/start`

Request
```metadata json
{
    "researchId": int
}
```

Response
```metadata json
true
```

**Player - Get research information**

`GET /api/v1/player/research/<researchId>`

Response
```metadata json
{
    "researchId": int,
    "name": string,
    "description": string,
    "points": int,
    "commodity": {
        "commodityId": int,
        "name": string
    }
}
```

**Player - Logout**

`POST /api/v1/player/logout`

Response
```metadata json
true
```
