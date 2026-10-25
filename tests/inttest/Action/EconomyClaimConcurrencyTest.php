<?php

declare(strict_types=1);

namespace Stu\Action;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Stu\Component\Quest\QuestUserModeEnum;
use Stu\Config\ConfigStageEnum;
use Stu\Config\Init;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Deals;
use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Entity\NPCQuestUser;
use Stu\Orm\Entity\TradeOffer;
use Stu\Orm\Entity\TradePost;
use Stu\Orm\Entity\User;
use Throwable;

final class EconomyClaimConcurrencyTest extends TestCase
{
    private EntityManagerInterface $source;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testConcurrentReadersWaitAndObserveCommittedOrRolledBackClaims(): void
    {
        $this->source = Init::getContainer(ConfigStageEnum::DEVELOPMENT)->get(EntityManagerInterface::class);
        $connection = $this->source->getConnection();
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
        $schema = 'core_security_' . bin2hex(random_bytes(8));
        $connection->executeStatement('CREATE SCHEMA ' . $connection->quoteIdentifier($schema));
        try {
            $em = $this->manager($schema);
            $isolated = new SchemaTool($em)->getSchemaFromMetadata($em->getMetadataFactory()->getAllMetadata());
            foreach ($isolated->getTables() as $table) {
                foreach ($table->getForeignKeys() as $foreignKey) {
                    $table->removeForeignKey($foreignKey->getName());
                }
            }
            foreach ($isolated->toSql($em->getConnection()->getDatabasePlatform()) as $sql) {
                $em->getConnection()->executeStatement($sql);
            }
            $em->getConnection()->close();
            $connection->close();

            foreach ([['quest', false], ['auction', false], ['offer', false], ['partial', false], ['quest', true], ['auction', true], ['offer', true]] as [$kind, $rollback]) {
                $this->checkConcurrentClaim($schema, $kind, $rollback);
            }
        } finally {
            $connection->executeStatement('DROP SCHEMA ' . $connection->quoteIdentifier($schema) . ' CASCADE');
            $connection->close();
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

    private function checkConcurrentClaim(string $schema, string $kind, bool $rollback): void
    {
        $em = $this->manager($schema);
        $entity = match ($kind) {
            'quest' => new NPCQuestUser()->setQuest($em->getReference(NPCQuest::class, 1))->setQuestId(1)->setUser($em->getReference(User::class, 101))->setUserId(101)->setMode(QuestUserModeEnum::ACTIVE_MEMBER),
            'auction' => new Deals()->setAuction(true)->setStart(1)->setEnd(2)->setAuctionUser(101),
            'offer', 'partial' => new TradeOffer()->setUser($em->getReference(User::class, 101))->setTradePost($em->getReference(TradePost::class, 2))->setWantedCommodity($em->getReference(Commodity::class, 50))->setOfferedCommodity($em->getReference(Commodity::class, 2))->setOfferCount($kind === 'partial' ? 3 : 1)->setWantedCommodityCount(1)->setOfferedCommodityCount(1)
        };
        $em->persist($entity);
        $em->flush();
        $id = $entity->getId();
        $class = $entity::class;
        $em->getConnection()->close();
        $sockets = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        self::assertIsArray($sockets);
        foreach ($sockets as $socket) {
            stream_set_timeout($socket, 10);
        }
        $pid = pcntl_fork();
        self::assertNotSame(-1, $pid);
        if ($pid === 0) {
            fclose($sockets[0]);
            try {
                $worker = $this->manager($schema);
                $cached = $worker->find($class, $id);
                fwrite($sockets[1], json_encode(['loaded' => $this->state($cached, $kind)], JSON_THROW_ON_ERROR) . "\n");
                if (trim(fgets($sockets[1])) !== 'go') {
                    exit(2);
                }
                $worker->getConnection()->beginTransaction();
                fwrite($sockets[1], "started\n");
                $fresh = $this->locked($worker, $kind, $id);
                fwrite($sockets[1], json_encode(['fresh' => $this->state($fresh, $kind)], JSON_THROW_ON_ERROR) . "\n");
                $worker->getConnection()->commit();
                $worker->getConnection()->close();
                fclose($sockets[1]);
                exit(0);
            } catch (Throwable $error) {
                fwrite($sockets[1], json_encode(['error' => $error->getMessage()], JSON_THROW_ON_ERROR) . "\n");
                exit(1);
            }
        }

        fclose($sockets[1]);
        $em = $this->manager($schema);
        $connection = $em->getConnection();
        $status = null;
        try {
            $loaded = fgets($sockets[0]);
            self::assertNotFalse($loaded);
            self::assertArrayHasKey('loaded', json_decode($loaded, true, 512, JSON_THROW_ON_ERROR));
            $connection->beginTransaction();
            $current = $this->locked($em, $kind, $id);
            fwrite($sockets[0], "go\n");
            self::assertSame('started', trim(fgets($sockets[0])));
            $read = [$sockets[0]];
            $write = null;
            $except = null;
            self::assertSame(0, stream_select($read, $write, $except, 0, 150000), 'Der zweite Zugriff muss auf die Zeilensperre warten');
            match ($kind) {
                'quest' => $current->setRewardReceived(true),
                'auction' => $current->setTakenTime(12345),
                'partial' => $current->setOfferCount(2),
                'offer' => $em->remove($current)
            };
            $em->flush();
            if ($rollback) {
                $connection->rollBack();
            } else {
                $connection->commit();
            }
            $line = fgets($sockets[0]);
            self::assertNotFalse($line);
            $result = json_decode($line, true, 512, JSON_THROW_ON_ERROR);
            $expected = $rollback ? match ($kind) {
                'quest' => false, 'auction' => null, 'offer' => 1
            } : match ($kind) {
                'quest' => true, 'auction' => 12345, 'partial' => 2, 'offer' => null
            };
            self::assertSame(['fresh' => $expected], $result);
            self::assertSame($pid, pcntl_waitpid($pid, $status));
            self::assertTrue(pcntl_wifexited($status));
            self::assertSame(0, pcntl_wexitstatus($status));
            $em->clear();
            $remaining = $em->find($class, $id);
            if ($remaining !== null) {
                $em->remove($remaining);
                $em->flush();
            }
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

    private function locked(EntityManagerInterface $em, string $kind, int $id): ?object
    {
        return match ($kind) {
            'quest' => $em->getRepository(NPCQuestUser::class)->getByQuestAndUserForUpdate(1, 101),
            'auction' => $em->getRepository(Deals::class)->getForUpdate($id),
            'offer', 'partial' => $em->getRepository(TradeOffer::class)->getForUpdate($id)
        };
    }

    private function state(?object $entity, string $kind): bool|int|null
    {
        return match ($kind) {
            'quest' => $entity?->isRewardReceived(),
            'auction' => $entity?->getTakenTime(),
            'offer', 'partial' => $entity?->getOfferCount()
        };
    }
}
