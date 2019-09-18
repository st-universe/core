# DEFAULT
PHONY=init init-production tests update dev-serve dev-create-db dev-wipe-db dev-start-db dev-stop-db dev-migrate-db dirs i18nextract version_link migrateDatabase showDatabaseChanges

all:init dirs

dirs:force
	for a in src/admin/backup src/inc/generated src/html/avatare/user src/html/avatare/alliance; do mkdir -p "$$a"; chmod 770 "$$a"; done

generators:force
	for a in fieldnamedefines.inc.php; do php -f src/admin/generators/"$$a" $(ENV); done

i18nextract:force
	xgettext --no-location --no-wrap --from-code UTF-8 **/*.php -o php.pot
	i18nfool-extract html/*html
	head -n17 php.pot > tal.pot
	cat default.pot >> tal.pot
	msgcat php.pot tal.pot --no-location --no-wrap | sed -e 's/msgstr ".*/msgstr ""/' > lang/stu.pot
	rm default.pot php.pot tal.pot

init:force
	composer install

init-production:force
	composer install -a --no-dev

tests:force
	./vendor/bin/phpunit tests

update:force
	composer update

dev-serve:force
	php -S localhost:1337 -t src/

dev-create-db:force
	docker-compose up -d
	sleep 15
	docker-compose exec -T stu3-db sh -c 'exec mysql -ustu stu_db -pstu' < dist/db/stu.sql

dev-wipe-db:force
	docker-compose down

dev-start-db:force
	docker-compose start

dev-stop-db:force
	docker-compose stop

version_link:force
	cd src && ln -s . version_dev

clearCache:force
	vendor/bin/doctrine orm:clear-cache:metadata
	vendor/bin/doctrine orm:clear-cache:query
	vendor/bin/doctrine orm:clear-cache:result

migrateDatabase:force
	vendor/bin/doctrine orm:schema-tool:update --force
	vendor/bin/doctrine orm:generate-proxies

showDatabaseChanges:force
	vendor/bin/doctrine orm:schema-tool:update --dump-sql

force:
