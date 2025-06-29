#!/bin/bash

rm -rf src/Migrations/Sqlite/*
rm -rf inttest.sqlite

php vendor/bin/doctrine-migrations diff --stage=dev --configuration="config/migrations/development.php" --namespace="Stu\Migrations\Pgsql" --allow-empty-diff --no-interaction
php vendor/bin/doctrine-migrations diff --stage=inttest --configuration="config/migrations/development.php" --namespace="Stu\Migrations\Sqlite" --allow-empty-diff --no-interaction --from-empty-schema
