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

Danach sollte die Software via http://localhost:1337 erreichbar sein

API
---

Alle Namesspaces der API, mit Ausnahme von `common`, erwarten eine Authentifizierung mittels des über `common/login` zu bekommenden Tokens.
Dieser Token muss mittels `Authorization`-Header und dem Inhalt `Bearer <TOKEN>` bei jedem Request übergeben werden.

**Common - News**

`GET /v1/common/news`

Response
```$json
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

**Common - Login**

`POST /v1/common/login`

Request
```$json
{
    "username": "john",
    "password": "doe"
}
```

Response
```$json
{
    "statusCode": 200,
    "data": {
        "token": "sample-token
    }
}
```
