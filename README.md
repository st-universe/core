STU3
====

Lokales Setup
-------------

Benoetigte Software: Linux (geht u.U. auch unter macOS) docker, php 7.3,
composer

- Repository forken
- Source Code auschecken und folgende Befehle ausfuehren
- config.dist.json nach config.json kopieren und anpassen
- make int
- make dev-create-db
- make generators
- make dev-serve

Danach sollte die Software via http://localhost:1337 erreichbar sein
