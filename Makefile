# DEFAULT
PHONY=init init-production tests update dev-serve dev-create-db dev-wipe-db dev-start-db dev-stop-db dev-migrate-db data dirs i18nextract version_link

all:init dirs data

dirs:force
	for a in admin/backup inc/generated html/avatare html/avatare/user html/avatare/alliance; do mkdir -p "$$a"; chmod 770 "$$a"; done

generators:force
	for a in fieldnamedefines.inc.php crewraces.inc.php systemnames.inc.php; do php -f src/admin/generators/"$$a" $(ENV); done

data:force
	cd src/data && $(MAKE) -f genmakefile.mk
	cd src/data && $(MAKE)

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
	docker-compose exec -T stu3-db sh -c 'exec mysql -ustu stu_db -pstu' < admin/dist/stu3.sql

dev-wipe-db:force
	docker-compose down

dev-start-db:force
	docker-compose start

dev-stop-db:force
	docker-compose stop

version_link:force
	ln -s . dev

force:
force:

