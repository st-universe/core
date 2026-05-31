<?php

declare(strict_types=1);

namespace Stu\Component\Refactor;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Table;

final class RefactorRunner
{
    public function __construct(private Connection $connection) {}

    public function refactor(): void
    {
        $this->copyShipLogsToSpacecraftLogs();
        $this->backfillSpacecraftLogSnapshots();
    }

    private function copyShipLogsToSpacecraftLogs(): void
    {
        $schemaManager = $this->connection->createSchemaManager();
        if (
            !$schemaManager->tableExists('stu_ship_log')
            || !$schemaManager->tableExists('stu_spacecraft_log')
        ) {
            return;
        }

        $shipLogTable = $schemaManager->introspectTableByUnquotedName('stu_ship_log');
        $spacecraftLogTable = $schemaManager->introspectTableByUnquotedName('stu_spacecraft_log');

        if (
            !$this->tableHasColumns($shipLogTable, ['id', 'spacecraft_id', 'text', 'date', 'is_private', 'deleted'])
            || !$this->tableHasColumns($spacecraftLogTable, ['id', 'spacecraft_id', 'text', 'date', 'is_private', 'deleted'])
        ) {
            return;
        }

        $this->connection->executeStatement(
            'INSERT INTO stu_spacecraft_log (id, spacecraft_id, text, date, is_private, deleted)
            SELECT sl.id, sl.spacecraft_id, sl.text, sl.date, sl.is_private, sl.deleted
            FROM stu_ship_log sl
            WHERE NOT EXISTS (
                SELECT 1
                FROM stu_spacecraft_log scl
                WHERE scl.id = sl.id
            )'
        );

        $this->resetSpacecraftLogSequence();
    }

    private function backfillSpacecraftLogSnapshots(): void
    {
        $schemaManager = $this->connection->createSchemaManager();
        if (!$schemaManager->tableExists('stu_spacecraft_log')) {
            return;
        }

        $spacecraftLogTable = $schemaManager->introspectTableByUnquotedName('stu_spacecraft_log');
        foreach (['name', 'rump_id', 'user_id'] as $column) {
            if (!$spacecraftLogTable->hasColumn($column)) {
                return;
            }
        }

        $this->connection->executeStatement(
            'UPDATE stu_spacecraft_log scl
            SET name = sp.name,
                rump_id = sp.rump_id,
                user_id = sp.user_id
            FROM stu_spacecraft sp
            WHERE scl.spacecraft_id = sp.id
            AND (scl.name IS NULL OR scl.rump_id IS NULL OR scl.user_id IS NULL)'
        );
    }

    /**
     * @param list<string> $columns
     */
    private function tableHasColumns(Table $table, array $columns): bool
    {
        foreach ($columns as $column) {
            if (!$table->hasColumn($column)) {
                return false;
            }
        }

        return true;
    }

    private function resetSpacecraftLogSequence(): void
    {
        if (!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform) {
            return;
        }

        $this->connection->executeStatement(
            "SELECT setval(
                pg_get_serial_sequence('stu_spacecraft_log', 'id'),
                COALESCE((SELECT MAX(id) FROM stu_spacecraft_log), 1),
                (SELECT COUNT(*) > 0 FROM stu_spacecraft_log)
            )"
        );
    }
}
