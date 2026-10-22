<?php

declare(strict_types=1);

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
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

require __DIR__ . '/../../vendor/autoload.php';

function manager(string $schema): EntityManagerInterface
{
    $source = Init::getContainer(ConfigStageEnum::DEVELOPMENT)->get(EntityManagerInterface::class);
    $params = $source->getConnection()->getParams();
    if (!in_array($params['host'] ?? '', ['localhost', '127.0.0.1', '::1'], true)) {
        throw new RuntimeException('The concurrency fixture requires a local PostgreSQL server');
    }
    $connection = DriverManager::getConnection($params);
    if (!$connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQLPlatform) {
        throw new RuntimeException('The concurrency fixture requires PostgreSQL');
    }
    $connection->executeStatement('SET search_path TO ' . $connection->quoteIdentifier($schema));
    $connection->executeStatement("SET lock_timeout = '5s'");
    $connection->executeStatement("SET statement_timeout = '10s'");
    return new EntityManager($connection, $source->getConfiguration());
}

function locked(EntityManagerInterface $em, string $kind, int $id): object|null
{
    return match ($kind) {
        'quest' => $em->getRepository(NPCQuestUser::class)->getByQuestAndUserForUpdate(1, 101),
        'auction' => $em->getRepository(Deals::class)->getForUpdate($id),
        'offer', 'partial' => $em->getRepository(TradeOffer::class)->getForUpdate($id)
    };
}

function state(object|null $entity, string $kind): bool|int|null
{
    return match ($kind) {
        'quest' => $entity?->isRewardReceived(),
        'auction' => $entity?->getTakenTime(),
        'offer', 'partial' => $entity?->getOfferCount()
    };
}

if (($argv[1] ?? '') === 'worker') {
    $em = manager($argv[2]);
    $kind = $argv[3];
    $id = (int)$argv[4];
    $class = match ($kind) {
        'quest' => NPCQuestUser::class,
        'auction' => Deals::class,
        'offer', 'partial' => TradeOffer::class
    };
    $cached = $em->find($class, $id);
    fwrite(STDOUT, json_encode(['loaded' => state($cached, $kind)], JSON_THROW_ON_ERROR) . "\n");
    if (trim(fgets(STDIN)) !== 'go') {
        exit(2);
    }
    $em->getConnection()->beginTransaction();
    fwrite(STDOUT, "started\n");
    $fresh = locked($em, $kind, $id);
    fwrite(STDOUT, json_encode(['fresh' => state($fresh, $kind)], JSON_THROW_ON_ERROR) . "\n");
    $em->getConnection()->commit();
    exit;
}

$schema = 'core_security_' . bin2hex(random_bytes(8));
$em = manager($schema);
$connection = $em->getConnection();
$connection->executeStatement('CREATE SCHEMA ' . $connection->quoteIdentifier($schema));
$checks = 0;
try {
    $metadata = $em->getMetadataFactory()->getAllMetadata();
    $isolated = new SchemaTool($em)->getSchemaFromMetadata($metadata);
    foreach ($isolated->getTables() as $table) {
        foreach ($table->getForeignKeys() as $foreignKey) {
            $table->removeForeignKey($foreignKey->getName());
        }
    }
    foreach ($isolated->toSql($connection->getDatabasePlatform()) as $sql) {
        $connection->executeStatement($sql);
    }
    foreach ([['quest', false], ['auction', false], ['offer', false], ['partial', false], ['quest', true], ['auction', true], ['offer', true]] as [$kind, $rollback]) {
        $entity = match ($kind) {
            'quest' => new NPCQuestUser()->setQuest($em->getReference(NPCQuest::class, 1))->setQuestId(1)->setUser($em->getReference(User::class, 101))->setUserId(101)->setMode(QuestUserModeEnum::ACTIVE_MEMBER),
            'auction' => new Deals()->setAuction(true)->setStart(1)->setEnd(2)->setAuctionUser(101),
            'offer', 'partial' => new TradeOffer()->setUser($em->getReference(User::class, 101))->setTradePost($em->getReference(TradePost::class, 2))->setWantedCommodity($em->getReference(Commodity::class, 50))->setOfferedCommodity($em->getReference(Commodity::class, 2))->setOfferCount($kind === 'partial' ? 3 : 1)->setWantedCommodityCount(1)->setOfferedCommodityCount(1)
        };
        $em->persist($entity);
        $em->flush();
        $id = $entity->getId();
        $process = proc_open([PHP_BINARY, __FILE__, 'worker', $schema, $kind, (string)$id], [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes);
        if (!is_resource($process)) {
            throw new RuntimeException('Unable to start independent database worker');
        }
        foreach ($pipes as $pipe) {
            stream_set_timeout($pipe, 10);
        }
        $loaded = fgets($pipes[1]);
        if ($loaded === false || !array_key_exists('loaded', json_decode($loaded, true, 512, JSON_THROW_ON_ERROR))) {
            throw new RuntimeException('Worker did not preload its entity');
        }
        $connection->beginTransaction();
        $current = locked($em, $kind, $id);
        fwrite($pipes[0], "go\n");
        if (trim(fgets($pipes[1])) !== 'started') {
            throw new RuntimeException('Worker did not begin its locked read');
        }
        $read = [$pipes[1]];
        $write = null;
        $except = null;
        if (stream_select($read, $write, $except, 0, 150000) !== 0) {
            throw new RuntimeException('Concurrent reader bypassed the row lock');
        }
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
        $line = fgets($pipes[1]);
        $result = $line === false ? null : json_decode($line, true, 512, JSON_THROW_ON_ERROR);
        $expected = $rollback ? match ($kind) {
            'quest' => false, 'auction' => null, 'offer' => 1
        } : match ($kind) {
            'quest' => true, 'auction' => 12345, 'partial' => 2, 'offer' => null
        };
        if (!is_array($result) || $result['fresh'] !== $expected) {
            throw new RuntimeException('Concurrent read did not see the committed state');
        }
        fclose($pipes[0]);
        fclose($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        if (proc_close($process) !== 0 || $errors !== '') {
            throw new RuntimeException('Independent worker reported a failure');
        }
        $checks++;
        $em->clear();
        if ($kind === 'quest') {
            $connection->executeStatement('TRUNCATE stu_npc_quest_user');
        }
    }
    echo sprintf("OK: %d PostgreSQL concurrency cases, including fresh state after waiting and rollback\n", $checks);
} finally {
    if ($connection->isTransactionActive()) {
        $connection->rollBack();
    }
    $connection->executeStatement('DROP SCHEMA ' . $connection->quoteIdentifier($schema) . ' CASCADE');
}
