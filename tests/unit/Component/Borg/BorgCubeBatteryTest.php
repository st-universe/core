<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;
use RuntimeException;
use Stu\Component\Spacecraft\System\Data\EpsSystemData;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\StuTime;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCauseEnum;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Template\StatusBarFactoryInterface;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\BorgSphereRepositoryInterface;
use Stu\Orm\Repository\SpacecraftSystemRepositoryInterface;
use Stu\StuTestCase;

final class BorgCubeBatteryTest extends StuTestCase
{
    private BorgCubeBattery $subject;
    private SpacecraftSystemRepositoryInterface&MockInterface $systems;
    private int $now = 1000;

    /** @var array<int, BorgSphereEvent> */
    private array $events = [];

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->systems = $this->mock(SpacecraftSystemRepositoryInterface::class);
        $this->systems->shouldReceive('save');
        $repository = $this->mock(BorgSphereRepositoryInterface::class);
        $repository->shouldReceive('getByShip')->andReturnUsing(fn (Spacecraft $ship): ?BorgSphereEvent => $this->events[$ship->getId()] ?? null);
        $repository->shouldReceive('lockEvent', 'save');
        $time = $this->mock(StuTime::class);
        $time->shouldReceive('time')->andReturnUsing(fn (): int => $this->now);
        $combat = new BorgSphereCombat($repository, new BorgSphereAttackContext(), $time);
        $this->subject = new BorgCubeBattery($combat, $time);
    }

    /** @return iterable<string, array{int, int, int, int, int, int}> */
    public static function batteryLoads(): iterable
    {
        yield 'fills only free EPS space' => [100, 250, 80, 300, 30, 4000];
        yield 'uses only the remaining battery' => [100, 290, 7, 297, 0, 1420];
        yield 'empty EPS is restored before combat participants are selected' => [100, 0, 100, 100, 0, 7000];
        yield 'damaged EPS capacity limits the transfer' => [25, 20, 80, 75, 25, 4300];
        yield 'full EPS keeps the cooldown and battery' => [25, 75, 80, 75, 80, 99999];
        yield 'EPS above reduced capacity never removes energy' => [25, 100, 80, 100, 80, 99999];
        yield 'empty battery cannot create energy' => [100, 20, 0, 20, 0, 99999];
        yield 'destroyed EPS cannot receive battery energy' => [0, 0, 80, 0, 80, 99999];
    }

    #[DataProvider('batteryLoads')]
    public function testDefenseTransfersOnlyAvailableBatteryIntoCurrentEpsCapacity(int $status, int $energy, int $battery, int $expectedEnergy, int $expectedBattery, int $expectedCooldown): void
    {
        [$ship, $wrapper, $eps] = $this->cube();
        $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::EPS)->setStatus($status);
        $eps->setEps($energy)->setBattery($battery)->setBatteryCooldown(99999);
        $messages = $this->mock(MessageCollectionInterface::class);

        $result = $this->subject->withDefense($this->party($wrapper), SpacecraftAttackCauseEnum::SHIP_FIGHT, function () use ($eps, $expectedEnergy, $messages): MessageCollectionInterface {
            self::assertSame($expectedEnergy, $eps->getEps());
            return $messages;
        });

        self::assertSame($messages, $result);
        self::assertSame($expectedEnergy, $eps->getEps());
        self::assertSame($expectedBattery, $eps->getBattery());
        self::assertSame($expectedCooldown, $eps->getBatteryCooldown());
        if ($expectedBattery !== $battery) {
            $stored = json_decode($ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::EPS)->getData(), true, 512, JSON_THROW_ON_ERROR);
            self::assertSame($expectedEnergy, $stored['eps']);
            self::assertSame($expectedBattery, $stored['battery']);
            self::assertSame($expectedCooldown, $stored['batteryCooldown']);
            $this->systems->shouldHaveReceived('save')->once();
        } else {
            $this->systems->shouldNotHaveReceived('save');
        }
    }

    public function testEveryDischargeIgnoresAndReplacesTheExistingCooldownWithoutRefillingTheBattery(): void
    {
        [, $wrapper, $eps] = $this->cube();
        $eps->setEps(295)->setBattery(20)->setBatteryCooldown(99999);

        $this->subject->withDefense($this->party($wrapper), SpacecraftAttackCauseEnum::SHIP_FIGHT, function () use ($wrapper, $eps): MessageCollectionInterface {
            self::assertSame(300, $eps->getEps());
            self::assertSame(15, $eps->getBattery());
            self::assertSame(1300, $eps->getBatteryCooldown());
            $this->now = 1001;
            $eps->lowerEps(2);
            $this->subject->discharge($wrapper);
            self::assertSame(300, $eps->getEps());
            self::assertSame(13, $eps->getBattery());
            self::assertSame(1121, $eps->getBatteryCooldown());
            $this->now = 1002;
            $eps->lowerEps(20);
            $this->subject->rechargeDefenders();
            self::assertSame(293, $eps->getEps());
            self::assertSame(0, $eps->getBattery());
            self::assertSame(1782, $eps->getBatteryCooldown());
            $eps->lowerEps(1);
            $this->subject->discharge($wrapper);
            self::assertSame(292, $eps->getEps());
            self::assertSame(1782, $eps->getBatteryCooldown());
            return $this->mock(MessageCollectionInterface::class);
        });

        $this->systems->shouldHaveReceived('save')->times(3);
    }

    public function testAWrapperOutsideTheDefendingPartyAndAllCallsOutsideTheScopeKeepTheirBattery(): void
    {
        [, $wrapper, $eps] = $this->cube();
        [, $otherWrapper, $otherEps] = $this->cube(102);
        $eps->setEps(290)->setBattery(20);
        $otherEps->setEps(250)->setBattery(80);
        $this->subject->discharge($wrapper);
        self::assertSame(290, $eps->getEps());

        $this->subject->withDefense($this->party($wrapper), SpacecraftAttackCauseEnum::SHIP_FIGHT, function () use ($otherWrapper, $otherEps): MessageCollectionInterface {
            $this->subject->discharge($otherWrapper);
            self::assertSame(250, $otherEps->getEps());
            self::assertSame(80, $otherEps->getBattery());
            return $this->mock(MessageCollectionInterface::class);
        });

        $eps->lowerEps(5);
        $this->subject->discharge($wrapper);
        $this->subject->rechargeDefenders();
        self::assertSame(295, $eps->getEps());
        self::assertSame(10, $eps->getBattery());
    }

    public function testCurrentEpsDamageIsRespectedByLaterDischargesInTheSameBattle(): void
    {
        [$ship, $wrapper, $eps] = $this->cube();
        $eps->setEps(290)->setBattery(80);

        $this->subject->withDefense($this->party($wrapper), SpacecraftAttackCauseEnum::SHIP_FIGHT, function () use ($ship, $wrapper, $eps): MessageCollectionInterface {
            $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::EPS)->setStatus(25);
            $this->subject->discharge($wrapper);
            self::assertSame(300, $eps->getEps());
            self::assertSame(70, $eps->getBattery());
            $eps->setEps(20);
            $this->subject->rechargeDefenders();
            self::assertSame(75, $eps->getEps());
            self::assertSame(15, $eps->getBattery());
            self::assertSame(4300, $eps->getBatteryCooldown());
            return $this->mock(MessageCollectionInterface::class);
        });
    }

    public function testMissingEpsMetadataDoesNotConsumeBatteryOrSetACooldown(): void
    {
        [, $wrapper, $eps] = $this->cube(hasData: false);
        $eps->setEps(250)->setBattery(80)->setBatteryCooldown(99999);

        $this->subject->withDefense($this->party($wrapper), SpacecraftAttackCauseEnum::SHIP_FIGHT, fn (): MessageCollectionInterface => $this->mock(MessageCollectionInterface::class));

        self::assertSame(250, $eps->getEps());
        self::assertSame(80, $eps->getBattery());
        self::assertSame(99999, $eps->getBatteryCooldown());
        $this->systems->shouldNotHaveReceived('save');
    }

    public function testScopeIsRemovedWhenTheBattleCallbackThrows(): void
    {
        [, $wrapper, $eps] = $this->cube();
        $eps->setEps(290)->setBattery(20);
        $exception = new RuntimeException('Battle failed');

        try {
            $this->subject->withDefense($this->party($wrapper), SpacecraftAttackCauseEnum::SHIP_FIGHT, static function () use ($exception): never {
                throw $exception;
            });
            self::fail('The exception must leave the battery scope');
        } catch (RuntimeException $caught) {
            self::assertSame($exception, $caught);
        }

        $eps->lowerEps(5);
        $this->subject->discharge($wrapper);
        self::assertSame(295, $eps->getEps());
        self::assertSame(10, $eps->getBattery());
    }

    public function testNestedBattlesTemporarilyReplaceAndThenRestoreTheOriginalDefenderScope(): void
    {
        [, $outerWrapper, $outerEps] = $this->cube();
        [, $innerWrapper, $innerEps] = $this->cube(102);
        $outerEps->setEps(290)->setBattery(50);
        $innerEps->setEps(290)->setBattery(50);

        $this->subject->withDefense($this->party($outerWrapper), SpacecraftAttackCauseEnum::SHIP_FIGHT, function () use ($outerWrapper, $outerEps, $innerWrapper, $innerEps): MessageCollectionInterface {
            $outerEps->lowerEps(5);
            $this->subject->withDefense($this->party($innerWrapper), SpacecraftAttackCauseEnum::SHIP_FIGHT, function () use ($outerWrapper, $outerEps, $innerWrapper, $innerEps): MessageCollectionInterface {
                self::assertSame(300, $innerEps->getEps());
                $this->subject->discharge($outerWrapper);
                self::assertSame(295, $outerEps->getEps());
                $innerEps->lowerEps(3);
                $this->subject->discharge($innerWrapper);
                self::assertSame(300, $innerEps->getEps());
                return $this->mock(MessageCollectionInterface::class);
            });
            $innerEps->lowerEps(4);
            $this->subject->discharge($innerWrapper);
            self::assertSame(296, $innerEps->getEps());
            $this->subject->discharge($outerWrapper);
            self::assertSame(300, $outerEps->getEps());
            self::assertSame(35, $outerEps->getBattery());
            return $this->mock(MessageCollectionInterface::class);
        });
    }

    /** @return iterable<string, array{SpacecraftAttackCauseEnum}> */
    public static function specialCauses(): iterable
    {
        foreach (SpacecraftAttackCauseEnum::cases() as $cause) {
            if ($cause !== SpacecraftAttackCauseEnum::SHIP_FIGHT) {
                yield $cause->name => [$cause];
            }
        }
    }

    #[DataProvider('specialCauses')]
    public function testSpecialAttackCausesDoNotUnlockTheBattery(SpacecraftAttackCauseEnum $cause): void
    {
        [, $wrapper, $eps] = $this->cube();
        $eps->setEps(250)->setBattery(80);

        $this->subject->withDefense($this->party($wrapper), $cause, function () use ($wrapper): MessageCollectionInterface {
            $this->subject->discharge($wrapper);
            return $this->mock(MessageCollectionInterface::class);
        });

        self::assertSame(250, $eps->getEps());
        self::assertSame(80, $eps->getBattery());
        $this->systems->shouldNotHaveReceived('save');
    }

    /** @return iterable<string, array{string}> */
    public static function excludedShips(): iterable
    {
        foreach (['ordinary player ship', 'player cube', 'sphere', 'assimilated companion', 'disabled', 'destroyed', 'regenerating', 'finished event'] as $state) {
            yield $state => [$state];
        }
    }

    #[DataProvider('excludedShips')]
    public function testOnlyOperationalNpcEventCubesUseTheBattery(string $state): void
    {
        [$ship, $wrapper, $eps] = $this->cube(userId: in_array($state, ['ordinary player ship', 'player cube'], true) ? 261 : 19);
        $eps->setEps(250)->setBattery(80);
        if ($state === 'sphere' || $state === 'ordinary player ship') {
            $this->events[$ship->getId()]->getConfiguration()->setSettings(['assimilation_enabled' => 0]);
        } elseif ($state === 'assimilated companion') {
            unset($this->events[$ship->getId()]);
        } elseif ($state === 'disabled' || $state === 'regenerating') {
            $ship->getCondition()->setDisabled(true);
            if ($state === 'regenerating') {
                $this->events[$ship->getId()]->setPhase(BorgSpherePhaseEnum::REGENERATING);
            }
        } elseif ($state === 'destroyed') {
            $ship->getCondition()->setIsDestroyed(true);
        } elseif ($state === 'finished event') {
            $this->events[$ship->getId()]->setPhase(BorgSpherePhaseEnum::ESCAPED);
        }

        $this->subject->withDefense($this->party($wrapper), SpacecraftAttackCauseEnum::SHIP_FIGHT, function () use ($wrapper): MessageCollectionInterface {
            $this->subject->discharge($wrapper);
            return $this->mock(MessageCollectionInterface::class);
        });

        self::assertSame(250, $eps->getEps());
        self::assertSame(80, $eps->getBattery());
        $this->systems->shouldNotHaveReceived('save');
    }

    public function testDeactivationDuringTheBattleImmediatelyStopsFurtherBatteryTransfers(): void
    {
        [$ship, $wrapper, $eps] = $this->cube();
        $eps->setEps(290)->setBattery(50);

        $this->subject->withDefense($this->party($wrapper), SpacecraftAttackCauseEnum::SHIP_FIGHT, function () use ($ship, $wrapper, $eps): MessageCollectionInterface {
            $eps->lowerEps(5);
            $ship->getCondition()->setDisabled(true);
            $this->events[$ship->getId()]->setPhase(BorgSpherePhaseEnum::REGENERATING);
            $this->subject->discharge($wrapper);
            $this->subject->rechargeDefenders();
            self::assertSame(295, $eps->getEps());
            self::assertSame(40, $eps->getBattery());
            return $this->mock(MessageCollectionInterface::class);
        });
    }

    /** @return array{Ship, SpacecraftWrapperInterface&MockInterface, EpsSystemData} */
    private function cube(int $id = 101, int $userId = 19, bool $hasData = true): array
    {
        $user = new User();
        new ReflectionProperty(User::class, 'id')->setValue($user, $userId);
        $ship = new Ship()->setUser($user);
        new ReflectionProperty(Spacecraft::class, 'id')->setValue($ship, $id);
        $ship->setCondition(new SpacecraftCondition($ship));
        $system = new SpacecraftSystem()->setSpacecraft($ship)->setSystemType(SpacecraftSystemTypeEnum::EPS)->setStatus(100)->setMode(SpacecraftSystemModeEnum::MODE_ON);
        $ship->getSystems()->set(SpacecraftSystemTypeEnum::EPS->value, $system);
        $eps = new EpsSystemData($this->systems, $this->mock(StatusBarFactoryInterface::class));
        $eps->setSpacecraft($ship);
        $eps->setMaxEps(300)->setEps(300)->setBattery(100);
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $wrapper->shouldReceive('get')->andReturn($ship);
        $wrapper->shouldReceive('getEpsSystemData')->andReturn($hasData ? $eps : null);
        $this->events[$id] = new BorgSphereEvent()->setShipId($id)->setPhase(BorgSpherePhaseEnum::ROAMING)
            ->setConfiguration(new BorgSphereConfiguration()->setSettings(['assimilation_enabled' => 1]));
        return [$ship, $wrapper, $eps];
    }

    private function party(SpacecraftWrapperInterface ...$wrappers): BattlePartyInterface
    {
        $party = $this->mock(BattlePartyInterface::class);
        $party->shouldReceive('getActiveMembers')->andReturn(new ArrayCollection($wrappers));
        return $party;
    }
}
