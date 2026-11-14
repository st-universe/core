<?php

declare(strict_types=1);

namespace Stu\Action;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\ActionTestCase;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\Trumfield;

final class BorgDataNodeDestructionIntegrationTest extends ActionTestCase
{
    private EntityManagerInterface $em;
    private Ship $ship;
    private Commodity $nodes;

    #[Override]
    public function setUp(): void
    {
        parent::setUp();
        $container = $this->getContainer();
        $this->em = $container->get(EntityManagerInterface::class);
        $this->em->getConnection()->insert('stu_commodity', [
            'id' => CommodityTypeConstants::COMMODITY_BORG_DATA_NODE,
            'name' => 'Borg-Datenknoten',
            'sort' => 93,
            'view' => 1,
            'type' => 1,
            'npc_commodity' => 0,
            'bound' => 0,
            'cheatable' => 1
        ]);
        $this->nodes = $this->em->find(Commodity::class, CommodityTypeConstants::COMMODITY_BORG_DATA_NODE);
        $this->ship = $this->em->find(Ship::class, 42);
        $container->get(LeaveFleetInterface::class)->leaveFleet($this->ship);
    }

    /** @return iterable<string, array{bool}> */
    public static function storageStates(): iterable
    {
        yield 'new cargo immediately before destruction' => [false];
        yield 'previously saved cargo after hydration' => [true];
    }

    #[DataProvider('storageStates')]
    public function testNormalDestructionDiscardsDataNodesWhileOrdinaryCargoAndNanitesReachTheWreck(bool $hydrate): void
    {
        $container = $this->getContainer();
        $storage = $container->get(StorageManagerInterface::class);
        $nanites = new Commodity()->setName('Naniten für Datenknoten-Trümmertest');
        $this->em->persist($nanites);
        $this->em->flush();
        $shipId = $this->ship->getId();
        $naniteId = $nanites->getId();
        $ordinary = $this->em->find(Commodity::class, 2);
        $ordinaryAmount = $this->ship->getStorage()->get(2)?->getAmount() ?? 0;
        $storage->upperStorage($this->ship, $ordinary, 13);
        $storage->upperStorage($this->ship, $nanites, 7);
        $storage->upperStorage($this->ship, $this->nodes, 4);
        if ($hydrate) {
            $this->em->flush();
            $this->em->clear();
            $this->ship = $this->em->find(Ship::class, $shipId);
        }
        $wreckIds = array_map(static fn (Trumfield $wreck): int => $wreck->getId(), $this->em->getRepository(Trumfield::class)->findAll());
        $wrapper = $container->get(SpacecraftWrapperFactoryInterface::class)->wrapShip($this->ship);
        $this->ship->getCondition()->setIsDestroyed(true);

        $this->em->wrapInTransaction(fn () => $container->get(SpacecraftDestructionInterface::class)->destroy(null, $wrapper, SpacecraftDestructionCauseEnum::SELF_DESTRUCTION, new InformationWrapper()));
        $this->em->clear();

        self::assertNull($this->em->find(Ship::class, $shipId));
        $wrecks = array_values(array_filter($this->em->getRepository(Trumfield::class)->findAll(), static fn (Trumfield $wreck): bool => !in_array($wreck->getId(), $wreckIds, true)));
        self::assertCount(1, $wrecks);
        self::assertNull($wrecks[0]->getStorage()->get(CommodityTypeConstants::COMMODITY_BORG_DATA_NODE));
        self::assertSame(0, $this->em->getRepository(Storage::class)->count(['commodity' => CommodityTypeConstants::COMMODITY_BORG_DATA_NODE]));
        self::assertSame(7, $wrecks[0]->getStorage()->get($naniteId)?->getAmount());
        self::assertSame($ordinaryAmount + 13, $wrecks[0]->getStorage()->get(2)?->getAmount());
    }
}
