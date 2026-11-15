<?php

declare(strict_types=1);

namespace Stu\Action;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;
use Stu\ActionTestCase;
use Stu\Component\Borg\BorgAssimilationDestructionDataNode;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Module\Spacecraft\Lib\Destruction\Handler\ClearBorgAssimilation;
use Stu\Module\Spacecraft\Lib\Destruction\Handler\DiscardBorgDataNodes;
use Stu\Module\Spacecraft\Lib\Destruction\Handler\DropBorgAssimilationDataNode;
use Stu\Module\Spacecraft\Lib\Destruction\Handler\SpacecraftDestructionHandlerInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestruction;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftRemoverInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftAssimilated;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\Trumfield;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class BorgAssimilationDataNodeLootIntegrationTest extends ActionTestCase
{
    private EntityManagerInterface $em;
    private Ship $ship;
    private Ship $cube;
    private Commodity $nodes;
    private int $now;

    #[Override]
    public function setUp(): void
    {
        parent::setUp();
        $container = $this->getContainer();
        $this->em = $container->get(EntityManagerInterface::class);
        $this->now = $container->get(StuTime::class)->time();
        $this->em->getConnection()->insert('stu_commodity', [
            'id' => CommodityTypeConstants::COMMODITY_BORG_DATA_NODE,
            'name' => 'Borg-Datenknoten',
            'sort' => 93,
            'view' => 1,
            'type' => 1,
            'npc_commodity' => 0,
            'bound' => 1,
            'cheatable' => 1
        ]);
        $this->nodes = $this->em->find(Commodity::class, CommodityTypeConstants::COMMODITY_BORG_DATA_NODE);
        $this->ship = $this->em->find(Ship::class, 42);
        $this->cube = $this->em->find(Ship::class, 77);
        $container->get(LeaveFleetInterface::class)->leaveFleet($this->ship);
        $container->get(LeaveFleetInterface::class)->leaveFleet($this->cube);
        $this->cube->setLocation($this->ship->getLocation());
        if (!$this->cube->hasSpacecraftSystem(SpacecraftSystemTypeEnum::TRACTOR_BEAM)) {
            $system = new SpacecraftSystem()->setSpacecraft($this->cube)->setSystemType(SpacecraftSystemTypeEnum::TRACTOR_BEAM);
            $this->cube->getSystems()->set(SpacecraftSystemTypeEnum::TRACTOR_BEAM->value, $system);
            $this->em->persist($system);
        }
        $this->cube->getSpacecraftSystem(SpacecraftSystemTypeEnum::TRACTOR_BEAM)->setStatus(100)->setMode(SpacecraftSystemModeEnum::MODE_ON);
    }

    /** @return iterable<string, array{string, int, int, int, bool}> */
    public static function lootStates(): iterable
    {
        yield 'active assimilation drops one node despite an entirely full hold' => ['active', 10, 0, 1, false];
        yield 'completed assimilation replaces four saved cargo nodes by one new drop' => ['complete', 10, 4, 1, true];
        yield 'eleventh percentile discards old nodes without new loot' => ['complete', 11, 4, 0, false];
        yield 'freed partial assimilation never rolls new loot' => ['frozen', 0, 4, 0, true];
        yield 'ordinary ship cargo does not become destruction loot' => ['none', 0, 4, 0, false];
    }

    #[DataProvider('lootStates')]
    public function testRealDestructionDiscardsOldNodesThenMovesAtMostOneNewNodeToTheWreck(string $state, int $roll, int $oldNodes, int $expected, bool $hydrate): void
    {
        $container = $this->getContainer();
        $this->entry($state);
        $storage = $container->get(StorageManagerInterface::class);
        if ($oldNodes > 0) {
            $storage->upperStorage($this->ship, $this->nodes, $oldNodes);
        }
        $ordinary = $this->em->find(Commodity::class, 2);
        $storage->upperStorage($this->ship, $ordinary, 50);
        $ordinaryAmount = $this->ship->getStorage()->get(2)->getAmount();
        $this->ship->getRump()->setStorage($this->ship->getStorageSum());
        self::assertSame($this->ship->getMaxStorage(), $this->ship->getStorageSum());
        $shipId = $this->ship->getId();
        if ($hydrate) {
            $this->em->flush();
            $this->em->clear();
            $this->ship = $this->em->find(Ship::class, $shipId);
        }
        $wreckIds = array_map(static fn (Trumfield $wreck): int => $wreck->getId(), $this->em->getRepository(Trumfield::class)->findAll());
        $random = $this->mock(StuRandom::class);
        if ($state === 'active' || $state === 'complete') {
            $random->shouldReceive('rand')->once()->with(1, 100)->andReturn($roll);
        } else {
            $random->shouldNotReceive('rand');
        }
        $destruction = $this->destruction($random);
        $wrapper = $container->get(SpacecraftWrapperFactoryInterface::class)->wrapShip($this->ship);
        $this->ship->getCondition()->setIsDestroyed(true);
        $information = new InformationWrapper();

        $this->em->wrapInTransaction(fn () => $destruction->destroy(null, $wrapper, SpacecraftDestructionCauseEnum::SELF_DESTRUCTION, $information));
        $this->em->clear();

        self::assertNull($this->em->find(Ship::class, $shipId));
        self::assertNull($this->em->find(SpacecraftAssimilated::class, $shipId));
        $wrecks = array_values(array_filter($this->em->getRepository(Trumfield::class)->findAll(), static fn (Trumfield $wreck): bool => !in_array($wreck->getId(), $wreckIds, true)));
        self::assertCount(1, $wrecks);
        self::assertSame($expected, $wrecks[0]->getStorage()->get(CommodityTypeConstants::COMMODITY_BORG_DATA_NODE)?->getAmount() ?? 0);
        self::assertSame($expected, array_sum(array_map(static fn (Storage $entry): int => $entry->getAmount(), $this->em->getRepository(Storage::class)->findBy(['commodity' => CommodityTypeConstants::COMMODITY_BORG_DATA_NODE]))));
        self::assertSame($ordinaryAmount, $wrecks[0]->getStorage()->get(2)?->getAmount());
        self::assertSame($expected > 0, str_contains($information->getInformationsAsString(), '1 Borg-Datenknoten'));
    }

    public function testAdministrativeRemovalDoesNotProduceDataNodeLootOrAWreck(): void
    {
        $this->entry('complete');
        $this->em->flush();
        $shipId = $this->ship->getId();
        $wreckCount = $this->em->getRepository(Trumfield::class)->count([]);

        $this->em->wrapInTransaction(fn () => $this->getContainer()->get(SpacecraftRemoverInterface::class)->remove($this->ship, true));
        $this->em->clear();

        self::assertNull($this->em->find(Ship::class, $shipId));
        self::assertNull($this->em->find(SpacecraftAssimilated::class, $shipId));
        self::assertSame($wreckCount, $this->em->getRepository(Trumfield::class)->count([]));
        self::assertSame(0, $this->em->getRepository(Storage::class)->count(['commodity' => CommodityTypeConstants::COMMODITY_BORG_DATA_NODE]));
    }

    private function entry(string $state): void
    {
        if ($state === 'none') {
            return;
        }
        $entry = new SpacecraftAssimilated()->setSpacecraft($this->ship)->start($this->cube, $this->now - 300, 900)->setNaniteCommodityId(99999999);
        if ($state === 'complete') {
            $entry->complete($this->now);
        } elseif ($state === 'frozen') {
            $entry->freeze($this->now);
        } else {
            $this->cube->setTractoredShip($this->ship);
        }
        $this->em->persist($entry);
    }

    private function destruction(StuRandom $random): SpacecraftDestruction
    {
        $container = $this->getContainer();
        $drop = new DropBorgAssimilationDataNode($container->make(BorgAssimilationDestructionDataNode::class, ['random' => $random]));
        $configured = $container->get(SpacecraftDestructionInterface::class);
        $handlers = new ReflectionProperty(SpacecraftDestruction::class, 'destructionHandlers')->getValue($configured);
        self::assertInstanceOf(DiscardBorgDataNodes::class, $handlers[0]);
        self::assertInstanceOf(DropBorgAssimilationDataNode::class, $handlers[1]);
        self::assertInstanceOf(ClearBorgAssimilation::class, $handlers[2]);
        $handlers = array_map(static fn (SpacecraftDestructionHandlerInterface $handler): SpacecraftDestructionHandlerInterface => $handler instanceof DropBorgAssimilationDataNode ? $drop : $handler, $handlers);
        return new SpacecraftDestruction($container->get(SpacecraftRepositoryInterface::class), $handlers);
    }
}
