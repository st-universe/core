<?php

declare(strict_types=1);

namespace Stu\Config;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\PrimaryKeyConstraint;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\Exception\AbortMigration;
use Psr\Log\NullLogger;
use Stu\Migrations\Pgsql\Version20261112114702;
use Stu\StuTestCase;

require_once dirname(__DIR__, 3) . '/src/Migrations/pgsql/Version20261112114702.php';

final class NaniteProductionCleanupTest extends StuTestCase
{
    public function testRemovesObsoleteDataAndKeepsResearchAndComponentProduction(): void
    {
        $connection = $this->database(false);
        $migration = new Version20261112114702($connection, new NullLogger());
        $migration->up(new Schema());
        foreach ($migration->getSql() as $query) {
            $connection->executeStatement($query->getStatement(), $query->getParameters(), $query->getTypes());
        }

        self::assertSame([1, 3, 11], $connection->fetchFirstColumn('SELECT id FROM stu_technology_recipe ORDER BY id'));
        self::assertSame([2], $connection->fetchFirstColumn('SELECT id FROM stu_technology_production'));
        self::assertSame([2], $connection->fetchFirstColumn('SELECT id FROM stu_technology_recipe_cost'));
        self::assertSame(15, $connection->fetchOne('SELECT count FROM stu_modules_queue WHERE id = 1'));
        self::assertFalse(array_any(
            $connection->createSchemaManager()->introspectTableColumnsByUnquotedName('stu_technology_production'),
            static fn (Column $column): bool => $column->getObjectName()->toString() === 'production_tick'
        ));
    }

    public function testRefusesCleanupWhilePaidNaniteOrdersAreStillRunning(): void
    {
        $connection = $this->database(true);
        $migration = new Version20261112114702($connection, new NullLogger());
        $this->expectException(AbortMigration::class);
        $this->expectExceptionMessageIsOrContains('offene Nanitenaufträge');

        try {
            $migration->up(new Schema());
        } finally {
            self::assertSame([], $migration->getSql());
            self::assertSame(2, $connection->fetchOne('SELECT COUNT(*) FROM stu_technology_production'));
            self::assertSame(4, $connection->fetchOne('SELECT COUNT(*) FROM stu_technology_recipe'));
            self::assertSame(15, $connection->fetchOne('SELECT count FROM stu_modules_queue WHERE id = 1'));
        }
    }

    private function database(bool $running): Connection
    {
        $connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $connection->executeStatement('PRAGMA foreign_keys = ON');
        $schema = new Schema();
        $recipes = $schema->createTable('stu_technology_recipe');
        $recipes->addColumn('id', 'integer');
        $recipes->addColumn('kind', 'string');
        $recipes->addPrimaryKeyConstraint(PrimaryKeyConstraint::editor()->setUnquotedColumnNames('id')->create());
        $costs = $schema->createTable('stu_technology_recipe_cost');
        $costs->addColumn('id', 'integer');
        $costs->addColumn('recipe_id', 'integer');
        $costs->addPrimaryKeyConstraint(PrimaryKeyConstraint::editor()->setUnquotedColumnNames('id')->create());
        $costs->addForeignKeyConstraint('stu_technology_recipe', ['recipe_id'], ['id']);
        $production = $schema->createTable('stu_technology_production');
        $production->addColumn('id', 'integer');
        $production->addColumn('recipe_id', 'integer');
        $production->addColumn('kind', 'string');
        $production->addColumn('status', 'string');
        $production->addColumn('production_tick', 'boolean');
        $production->addPrimaryKeyConstraint(PrimaryKeyConstraint::editor()->setUnquotedColumnNames('id')->create());
        $production->addForeignKeyConstraint('stu_technology_recipe', ['recipe_id'], ['id']);
        $queues = $schema->createTable('stu_modules_queue');
        $queues->addColumn('id', 'integer');
        $queues->addColumn('count', 'integer');
        $queues->addPrimaryKeyConstraint(PrimaryKeyConstraint::editor()->setUnquotedColumnNames('id')->create());
        $manager = $connection->createSchemaManager();
        foreach ($schema->getTables() as $table) {
            $manager->createTable($table);
        }
        foreach ([1 => 'nanite_research', 2 => 'nanite_production', 3 => 'dataset_copy', 11 => 'module_optimization'] as $id => $kind) {
            $connection->insert('stu_technology_recipe', ['id' => $id, 'kind' => $kind]);
        }
        $connection->insert('stu_technology_recipe_cost', ['id' => 1, 'recipe_id' => 2]);
        $connection->insert('stu_technology_recipe_cost', ['id' => 2, 'recipe_id' => 3]);
        $connection->insert('stu_technology_production', ['id' => 1, 'recipe_id' => 2, 'kind' => 'nanite_production', 'status' => $running ? 'running' : 'completed', 'production_tick' => 1]);
        $connection->insert('stu_technology_production', ['id' => 2, 'recipe_id' => 3, 'kind' => 'dataset_copy', 'status' => 'running', 'production_tick' => 0]);
        $connection->insert('stu_modules_queue', ['id' => 1, 'count' => 15]);
        return $connection;
    }
}
