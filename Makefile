# DEFAULT
PHONY=init init-production tests coverage update dev-serve dev-create-db dev-wipe-db dev-start-db dev-stop-db dev-migrate-db dirs version_link migrateDatabase showDatabaseChanges

SUITE=

all:init dirs

dirs:force
	for a in src/admin/backup src/Public/avatare/user src/Public/avatare/alliance; do mkdir -p "$$a"; chmod 770 "$$a"; done

init:force
	composer install

init-production:force
	composer install -a --no-dev

tests:force
	./vendor/bin/phpunit tests $$SUITE

coverage:force
	./vendor/bin/phpunit -c phpunit-coverage.xml tests $$SUITE

update:force
	composer update

dev-serve:force
	php -S localhost:1337 -t src/Public/

dev-create-db:force
	docker-compose up -d stu-db
	sleep 15
	docker-compose exec -T stu-db sh -c 'exec psql -U stu -d stu_db < /dump/stu.dump'

dev-wipe-db:force
	docker-compose kill stu-db
	docker-compose rm -f stu-db

dev-start-db:force
	docker-compose up -d stu-db

dev-stop-db:force
	docker-compose kill stu-db

version_link:force
	cd src/Public && ln -s . version_dev

clearCache:force
	vendor/bin/doctrine orm:clear-cache:metadata
	vendor/bin/doctrine orm:clear-cache:query

migrateDatabase:force
	vendor/bin/doctrine orm:schema-tool:update --force
	vendor/bin/doctrine orm:generate-proxies

showDatabaseChanges:force
	vendor/bin/doctrine orm:schema-tool:update --dump-sql

force:
