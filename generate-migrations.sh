#!/bin/bash
php vendor/bin/doctrine-migrations diff --stage=dev --configuration="dist/db/migrations/development.php" --namespace="Stu\Migrations\Pgsql" --allow-empty-diff --no-interaction
php vendor/bin/doctrine-migrations diff --stage=inttest --configuration="dist/db/migrations/development.php" --namespace="Stu\Migrations\Sqlite" --allow-empty-diff --no-interaction --from-empty-schema
