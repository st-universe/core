# ST-Universe Source

## Lokales Setup

Benötigte Software: Linux (geht u.U. auch unter macOS) docker, php 7.4,
composer, git.

- Repository forken und das Source-Code auschecken
- config.dist.json nach config.json kopieren und anpassen
- make init
- vendor/bin/doctrine orm:generate-proxies
- make dev-create-db
- php src/admin/scripts/reset.php
- make dev-serve
- Use the cli command to create a new user `./bin/cli user:create --help`

Danach sollte die Software via http://localhost:1337 erreichbar sein, ggf. kann
es sein, dass der Hostname (stu-db) nicht aufgelöst werden kann. In diesem
Fall muss für die im `docker-compose.yml` File hinterlegte IP ein Eintrag in
der Hosts-Datei des Betriebssystems gemacht werden.

## Server-Deployment

Code:

- Repository auschecken
- make init-production
- make dirs
- rsync -rv --delete-after --exclude=assets --links --exclude=config.json --exclude=src/Public/admin/manage --exclude=src/Public/avatare/* src bin vendor Makefile cli-config.php config.dist.json /path/to/stuniverse-source/
- cd /path/to/stuniverse-source && make clearCache && make migrateDatabase

### Assets

- Repository auschecken
- php generator/building_generator/gen.php
- php generator/field_generator/generator.php
- rsync -rv --delete-after --exclude=dist --exclude=".git" . /path/to/stuniverse-source/assets/

## DB-Dump aus Backups einspielen

- aktuelles Schema umbenennen, z.B. in 'stuDamaged'
- neue Datenbank mit Original-Name erstellen, z.B. 'stu'
- auf Kommandozeile in den postgres user einloggen, mittels 'sudo su - postgres'
- in Backup-Folder wechseln, z.B: core/dist/db/backup
- Backup einspielen mittels 'pg_restore -d stu -U postgres -C dd-MM-yyyy.dump'

## Automatische Scripte (Rundenwechsel, etc)

Um alle automatisierte Scripte zu den definierten Zeiten laufen zu lassen, muss folgender `cronjob` hinzugefügt werden.

```shell
* * * * * cd /path/to/stu/core && vendor/bin/crunz schedule:run
```
