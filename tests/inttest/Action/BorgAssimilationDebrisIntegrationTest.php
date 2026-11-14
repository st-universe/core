<?php

declare(strict_types=1);

namespace Stu\Action;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\ActionTestCase;
use Stu\Component\Borg\BorgAssimilationDestructionNanites;
use Stu\Component\Borg\BorgAssimilationNanites;
use Stu\Component\Borg\BorgAssimilationService;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Module\Spacecraft\Lib\Destruction\Handler\ClearBorgAssimilation;
use Stu\Module\Spacecraft\Lib\Destruction\Handler\TransformToTrumfield;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestruction;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftRemoverInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftAssimilated;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\Trumfield;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class BorgAssimilationDebrisIntegrationTest extends ActionTestCase
{
    private EntityManagerInterface $em;
    private Ship $ship;
    private Ship $cube;
    private Commodity $nanites;
    private int $now;

    #[Override]
    public function setUp(): void
    {
        parent::setUp();
        $container = $this->getContainer();
        $this->em = $container->get(EntityManagerInterface::class);
        $this->now = $container->get(StuTime::class)->time();
        $this->ship = $this->em->find(Ship::class, 42);
        $this->cube = $this->em->find(Ship::class, 77);
        $container->get(LeaveFleetInterface::class)->leaveFleet($this->ship);
        $container->get(LeaveFleetInterface::class)->leaveFleet($this->cube);
        $this->cube->setLocation($this->ship->getLocation());
        $this->nanites = new Commodity()->setName('Naniten für Assimilationstrümmer');
        $this->em->persist($this->nanites);
        $this->em->flush();
    }

    /** @return iterable<string, array{string, int, int}> */
    public static function destructionStates(): iterable
    {
        yield 'active partial assimilation uses current progress' => ['active', 60, 2];
        yield 'completed assimilation yields full nanites' => ['complete', 900, 30];
        yield 'zero progress yields an empty nanite cargo' => ['active', 0, 0];
        yield 'released ship retains its previously granted cargo only' => ['frozen', 300, 5];
    }

    #[DataProvider('destructionStates')]
    public function testDestructionMovesNanitesIntoNormalDebrisAndDeletesTheAssimilationRecord(string $state, int $elapsed, int $expected): void
    {
        $container = $this->getContainer();
        $entry = $this->entry($state, $elapsed);
        if ($state === 'frozen') {
            $container->get(StorageManagerInterface::class)->upperStorage($this->ship, $this->nanites, 5);
        }
        $this->em->persist($entry);
        $this->em->flush();
        $shipId = $this->ship->getId();
        $locationId = $this->ship->getLocation()->getId();
        $wreckIds = array_map(static fn (Trumfield $wreck): int => $wreck->getId(), $this->em->getRepository(Trumfield::class)->findAll());
        $random = $this->mock(StuRandom::class);
        if ($state === 'frozen' || $elapsed === 0) {
            $random->shouldNotReceive('rand');
        } else {
            $random->shouldReceive('rand')->once()->with(1, 30)->andReturn(30);
        }
        $clock = $this->mock(StuTime::class);
        $clock->shouldReceive('time')->andReturn($this->now);
        $nanites = $container->make(BorgAssimilationNanites::class, ['random' => $random]);
        $loot = $container->make(BorgAssimilationDestructionNanites::class, ['nanites' => $nanites, 'time' => $clock]);
        $clear = new ClearBorgAssimilation($container->get(BorgAssimilationService::class), $loot);
        $destruction = new SpacecraftDestruction($container->get(SpacecraftRepositoryInterface::class), [$clear, $container->get(TransformToTrumfield::class)]);
        $wrapper = $container->get(SpacecraftWrapperFactoryInterface::class)->wrapShip($this->ship);
        $this->ship->getCondition()->setIsDestroyed(true);

        $this->em->wrapInTransaction(fn () => $destruction->destroy($this->cube, $wrapper, SpacecraftDestructionCauseEnum::SHIP_FIGHT, new InformationWrapper()));
        $this->em->clear();

        self::assertNull($this->em->find(Ship::class, $shipId));
        self::assertNull($this->em->find(SpacecraftAssimilated::class, $shipId));
        $wrecks = array_values(array_filter($this->em->getRepository(Trumfield::class)->findAll(), static fn (Trumfield $wreck): bool => !in_array($wreck->getId(), $wreckIds, true)));
        self::assertCount(1, $wrecks);
        self::assertSame($locationId, $wrecks[0]->getLocation()->getId());
        self::assertSame($expected, $wrecks[0]->getStorage()->get($this->nanites->getId())?->getAmount() ?? 0);
        self::assertNull($this->em->find(Ship::class, $this->cube->getId())->getTractoredShip());
    }

    public function testRemovingAShipWithoutDestructionDoesNotCreateNaniteLoot(): void
    {
        $entry = $this->entry('complete', 900);
        $this->em->persist($entry);
        $this->em->flush();
        $shipId = $this->ship->getId();
        $naniteId = $this->nanites->getId();
        $beforeWrecks = $this->em->getRepository(Trumfield::class)->count([]);

        $this->em->wrapInTransaction(fn () => $this->getContainer()->get(SpacecraftRemoverInterface::class)->remove($this->ship, true));
        $this->em->clear();

        self::assertNull($this->em->find(Ship::class, $shipId));
        self::assertNull($this->em->find(SpacecraftAssimilated::class, $shipId));
        self::assertSame($beforeWrecks, $this->em->getRepository(Trumfield::class)->count([]));
        self::assertSame(0, $this->em->getRepository(Storage::class)->count(['commodity' => $naniteId]));
    }

    private function entry(string $state, int $elapsed): SpacecraftAssimilated
    {
        $entry = new SpacecraftAssimilated()->setSpacecraft($this->ship)->start($this->cube, $this->now - $elapsed, 900)->setNaniteCommodityId($this->nanites->getId())->setNaniteReward(1, 30);
        if ($state === 'complete') {
            $entry->complete($this->now);
        } elseif ($state === 'frozen') {
            $entry->freeze($this->now);
        } else {
            $this->cube->setTractoredShip($this->ship);
            $system = $this->cube->getSystems()->get(SpacecraftSystemTypeEnum::TRACTOR_BEAM->value);
            if ($system === null) {
                $system = new SpacecraftSystem()->setSpacecraft($this->cube)->setSystemType(SpacecraftSystemTypeEnum::TRACTOR_BEAM);
                $this->cube->getSystems()->set(SpacecraftSystemTypeEnum::TRACTOR_BEAM->value, $system);
                $this->em->persist($system);
            }
            $system->setStatus(100)->setMode(SpacecraftSystemModeEnum::MODE_ON);
        }
        return $entry;
    }
}
