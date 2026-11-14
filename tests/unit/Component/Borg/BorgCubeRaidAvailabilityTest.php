<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Realtime\SpacecraftMovementPublisherInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Interaction\InteractionCheckerBuilderFactoryInterface;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Auxiliary\SpacecraftShutdownInterface;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Repository\BorgSphereRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\StuTestCase;

final class BorgCubeRaidAvailabilityTest extends StuTestCase
{
    private BorgSphereRepositoryInterface&MockInterface $events;
    private InteractionCheckerBuilderFactoryInterface&MockInterface $checks;
    private StorageManagerInterface&MockInterface $storage;
    private CommodityRepositoryInterface&MockInterface $commodities;
    private StuRandom&MockInterface $random;
    private BorgSphereRaidService $service;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->events = $this->mock(BorgSphereRepositoryInterface::class);
        $this->checks = $this->mock(InteractionCheckerBuilderFactoryInterface::class);
        $this->storage = $this->mock(StorageManagerInterface::class);
        $this->commodities = $this->mock(CommodityRepositoryInterface::class);
        $this->random = $this->mock(StuRandom::class);
        $team = new BorgSphereRaidTeam($this->mock(CrewRepositoryInterface::class), $this->mock(CrewAssignmentRepositoryInterface::class), $this->mock(SpacecraftShutdownInterface::class), $this->mock(SpacecraftMovementPublisherInterface::class), $this->random);
        $time = $this->mock(StuTime::class);
        $time->shouldReceive('time')->andReturn(1000);
        $this->service = new BorgSphereRaidService($this->events, $team, $this->checks, $this->commodities, $this->storage, $this->mock(PrivateMessageSenderInterface::class), $this->random, $time);
    }

    /** @return iterable<string, array{bool, BorgSpherePhaseEnum, bool, bool}> */
    public static function availability(): iterable
    {
        foreach ([false, true] as $cube) {
            foreach (BorgSpherePhaseEnum::cases() as $phase) {
                foreach ([false, true] as $disabled) {
                    yield ($cube ? 'cube' : 'sphere') . ' ' . $phase->name . ($disabled ? ' disabled' : ' active') => [$cube, $phase, $disabled, !$cube || ($phase === BorgSpherePhaseEnum::REGENERATING && $disabled)];
                }
            }
        }
    }

    #[DataProvider('availability')]
    public function testOnlyADisabledRegeneratingCubeAcceptsAwayTeamsWhileSphereRulesStayUnchanged(bool $cube, BorgSpherePhaseEnum $phase, bool $disabled, bool $expected): void
    {
        $configuration = new BorgSphereConfiguration()->setSettings($cube ? ['assimilation_enabled' => 1] : []);
        $event = new BorgSphereEvent()->setConfiguration($configuration)->setPhase($phase);
        $target = new Ship();
        $target->setCondition(new SpacecraftCondition($target)->setDisabled($disabled));

        self::assertSame($expected, $this->service->canSendAwayTeam($event, $target));
    }

    public function testForgedCollectRequestIsRejectedAfterTheLockBeforeAnyCrewStorageOrCooldownAccess(): void
    {
        $target = new Ship();
        $target->setCondition(new SpacecraftCondition($target)->setDisabled(true));
        $configuration = new BorgSphereConfiguration()->setQuest(new NPCQuest()->setStart(0))->setEndTime(2000)->setSettings(['assimilation_enabled' => 1]);
        $event = new BorgSphereEvent()->setConfiguration($configuration)->setPhase(BorgSpherePhaseEnum::REGENERATING);
        $this->events->shouldReceive('getByShip')->once()->with($target)->andReturn($event);
        $this->events->shouldReceive('lockEvent')->once()->with($event)->andReturnUsing(static function () use ($event, $target): void {
            $event->setPhase(BorgSpherePhaseEnum::ROAMING);
            $target->getCondition()->setDisabled(false);
        });
        $this->events->shouldNotReceive('getRaid', 'saveRaid');
        $this->checks->shouldNotReceive('createInteractionChecker');
        $this->storage->shouldNotReceive('upperStorage', 'lowerStorage');
        $this->commodities->shouldNotReceive('find');
        $this->random->shouldNotReceive('rand', 'array_rand');
        $source = $this->mock(Ship::class);
        $source->shouldNotReceive('getUser', 'getCrewAssignments', 'getStorage', 'getExcessCrewCount');
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $wrapper->shouldReceive('get')->once()->andReturn($source);
        $information = new InformationWrapper();

        self::assertFalse($this->service->collect($wrapper, $target, $information));
        self::assertSame(['Eine Außenmission zum Borg-Kubus ist nur möglich, solange er kampfunfähig ist'], $information->getInformations());
    }
}
