<?php

declare(strict_types=1);

namespace Stu\Action;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Stu\Component\Admin\Notification\FailureEmailSenderInterface;
use Stu\Component\Game\GameStateEnum;
use Stu\Config\ConfigStageEnum;
use Stu\Config\Init;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\GameStateInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Tick\Process\ProcessTickHandlerInterface;
use Stu\Module\Tick\Process\ProcessTickRunner;
use Stu\Module\Tick\TransactionTickRunner;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\UserRepository;
use Throwable;

final class ProcessTickLockConcurrencyTest extends TestCase
{
    private EntityManagerInterface $source;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testProcessTickWaitsBeforeLockingBorgStateAndCanInsertUserReferences(): void
    {
        $this->source = Init::getContainer(ConfigStageEnum::DEVELOPMENT)->get(EntityManagerInterface::class);
        $connection = $this->source->getConnection();
        $this->requireLocalPostgres($connection);
        $schema = 'core_borg_locks_' . bin2hex(random_bytes(8));
        $connection->executeStatement('CREATE SCHEMA ' . $connection->quoteIdentifier($schema));
        try {
            $setup = $this->manager($schema)->getConnection();
            $setup->executeStatement('CREATE TABLE stu_user (id INTEGER PRIMARY KEY)');
            $setup->executeStatement('CREATE TABLE borg_tick_event (id INTEGER PRIMARY KEY, changes INTEGER NOT NULL)');
            $setup->executeStatement('CREATE TABLE borg_tick_notice (user_id INTEGER NOT NULL REFERENCES stu_user (id))');
            $setup->executeStatement('INSERT INTO stu_user (id) VALUES (19), (261)');
            $setup->executeStatement('INSERT INTO borg_tick_event (id, changes) VALUES (1, 0)');
            $setup->close();
            $connection->close();
            $this->checkConcurrentTick($schema);
        } finally {
            $connection->executeStatement('DROP SCHEMA ' . $connection->quoteIdentifier($schema) . ' CASCADE');
            $connection->close();
        }
    }

    private function requireLocalPostgres(Connection $connection): void
    {
        if (!in_array($connection->getParams()['host'] ?? '', ['localhost', '127.0.0.1', '::1'], true)) {
            self::markTestSkipped('Der Parallelzugriffstest benötigt einen lokalen PostgreSQL-Server');
        }
        try {
            if (!$connection->getDatabasePlatform() instanceof PostgreSQLPlatform) {
                self::markTestSkipped('Der Parallelzugriffstest benötigt PostgreSQL');
            }
            $connection->getNativeConnection();
        } catch (ConnectionException) {
            self::markTestSkipped('Für den Parallelzugriffstest ist kein lokaler PostgreSQL-Server verfügbar');
        }
    }

    private function manager(string $schema): EntityManagerInterface
    {
        $connection = DriverManager::getConnection($this->source->getConnection()->getParams());
        $connection->executeStatement('SET search_path TO ' . $connection->quoteIdentifier($schema));
        $connection->executeStatement("SET lock_timeout = '5s'");
        $connection->executeStatement("SET statement_timeout = '10s'");
        return new EntityManager($connection, $this->source->getConfiguration());
    }

    private function checkConcurrentTick(string $schema): void
    {
        $sockets = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        self::assertIsArray($sockets);
        foreach ($sockets as $socket) {
            stream_set_timeout($socket, 10);
        }
        $pid = pcntl_fork();
        self::assertNotSame(-1, $pid);
        if ($pid === 0) {
            fclose($sockets[0]);
            $this->runWorker($schema, $sockets[1]);
        }
        fclose($sockets[1]);
        $connection = $this->manager($schema)->getConnection();
        $status = null;
        try {
            $line = fgets($sockets[0]);
            self::assertNotFalse($line);
            $worker = json_decode($line, true, 512, JSON_THROW_ON_ERROR);
            self::assertArrayHasKey('pid', $worker, $line);
            $connection->beginTransaction();
            $connection->executeQuery('SELECT id FROM stu_user WHERE id IN (19, 261) ORDER BY id FOR UPDATE')->fetchAllAssociative();
            fwrite($sockets[0], "go\n");
            $this->waitForBlockedConnection($connection, $worker['pid']);
            self::assertSame(0, (int) $connection->fetchOne('SELECT changes FROM borg_tick_event WHERE id = 1 FOR UPDATE NOWAIT'));
            $connection->executeStatement('UPDATE borg_tick_event SET changes = 7 WHERE id = 1');
            $connection->commit();
            $result = fgets($sockets[0]);
            self::assertNotFalse($result);
            self::assertSame(['finished' => true], json_decode($result, true, 512, JSON_THROW_ON_ERROR), $result);
            self::assertSame(8, (int) $connection->fetchOne('SELECT changes FROM borg_tick_event WHERE id = 1'));
            self::assertSame(2, (int) $connection->fetchOne('SELECT COUNT(*) FROM borg_tick_notice'));
            self::assertSame($pid, pcntl_waitpid($pid, $status));
            self::assertTrue(pcntl_wifexited($status));
            self::assertSame(0, pcntl_wexitstatus($status));
        } finally {
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }
            fclose($sockets[0]);
            if ($status === null) {
                pcntl_waitpid($pid, $status);
            }
            $connection->close();
        }
    }

    /** @param resource $socket */
    private function runWorker(string $schema, mixed $socket): never
    {
        try {
            $em = $this->manager($schema);
            fwrite($socket, json_encode(['pid' => (int) $em->getConnection()->fetchOne('SELECT pg_backend_pid()')], JSON_THROW_ON_ERROR) . "\n");
            self::assertSame('go', trim((string) fgets($socket)));
            $handler = $this->createMock(ProcessTickHandlerInterface::class);
            $handler->expects(self::once())->method('work')->willReturnCallback(function () use ($em): void {
                $connection = $em->getConnection();
                $changes = (int) $connection->fetchOne('SELECT changes FROM borg_tick_event WHERE id = 1 FOR UPDATE');
                $connection->executeStatement('UPDATE borg_tick_event SET changes = changes + 1 WHERE id = 1');
                $connection->executeStatement('INSERT INTO borg_tick_notice (user_id) VALUES (19), (261)');
                self::assertSame(7, $changes);
            });
            $runner = new ProcessTickRunner(
                $this->transactions($em),
                [$handler],
                new UserRepository($em, $em->getClassMetadata(User::class)),
                Init::getContainer(ConfigStageEnum::DEVELOPMENT)->get(StuConfigInterface::class)
            );
            $runner->run(1, 1);
            $em->getConnection()->close();
            fwrite($socket, json_encode(['finished' => true], JSON_THROW_ON_ERROR) . "\n");
            fclose($socket);
            exit(0);
        } catch (Throwable $error) {
            fwrite($socket, json_encode(['error' => $error->getMessage()], JSON_THROW_ON_ERROR) . "\n");
            fclose($socket);
            exit(1);
        }
    }

    private function transactions(EntityManagerInterface $em): TransactionTickRunner
    {
        $state = $this->createMock(GameStateInterface::class);
        $state->method('getGameState')->willReturn(GameStateEnum::ONLINE);
        $logger = $this->createMock(LoggerUtilFactoryInterface::class);
        $logger->method('getLoggerUtil')->willReturn($this->createStub(LoggerUtilInterface::class));
        return new TransactionTickRunner($this->createStub(FailureEmailSenderInterface::class), $state, $em, $logger);
    }

    private function waitForBlockedConnection(Connection $connection, int $pid): void
    {
        $deadline = microtime(true) + 3;
        do {
            $connection->executeQuery('SELECT pg_stat_clear_snapshot()')->free();
            if ((int) $connection->fetchOne("SELECT COUNT(*) FROM pg_stat_activity WHERE pid = ? AND wait_event_type = 'Lock'", [$pid]) === 1) {
                return;
            }
            usleep(10000);
        } while (microtime(true) < $deadline);
        self::fail('Der Process-Tick muss vor dem Sperren des Borg-Eintrags auf die Benutzersperre warten');
    }
}
