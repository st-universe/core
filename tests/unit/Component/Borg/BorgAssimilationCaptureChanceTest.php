<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Player\Relation\PlayerRelationDeterminatorInterface;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftAssimilated;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\BorgAssimilationRepositoryInterface;
use Stu\StuTestCase;

final class BorgAssimilationCaptureChanceTest extends StuTestCase
{
    private BorgAssimilationRepositoryInterface&MockInterface $repository;
    private LeaveFleetInterface&MockInterface $fleets;
    private PrivateMessageSenderInterface&MockInterface $messages;
    private StuRandom&MockInterface $random;
    private StuTime&MockInterface $time;
    private BorgAssimilationCapture $capture;
    private BorgSphereEvent $event;
    private Ship $source;
    private Ship $target;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $location = new StarSystemMap();
        $this->source = $this->ship(19, $location);
        $this->target = $this->ship(261, $location);
        $location->getSpacecrafts()->add($this->target);
        $system = new SpacecraftSystem()->setSpacecraft($this->source)->setSystemType(SpacecraftSystemTypeEnum::TRACTOR_BEAM)->setStatus(100)->setMode(SpacecraftSystemModeEnum::MODE_OFF);
        $this->source->getSystems()->set(SpacecraftSystemTypeEnum::TRACTOR_BEAM->value, $system);
        $this->event = new BorgSphereEvent()->setConfiguration(new BorgSphereConfiguration()->setSettings(['assimilation_enabled' => 1, 'nanite_commodity_id' => 76]));
        $this->repository = $this->mock(BorgAssimilationRepositoryInterface::class);
        $this->fleets = $this->mock(LeaveFleetInterface::class);
        $this->messages = $this->mock(PrivateMessageSenderInterface::class);
        $this->random = $this->mock(StuRandom::class);
        $this->time = $this->mock(StuTime::class);
        $relations = $this->mock(PlayerRelationDeterminatorInterface::class);
        $relations->shouldReceive('isFriend')->andReturn(false);
        $this->capture = new BorgAssimilationCapture($this->repository, $relations, $this->fleets, $this->messages, $this->random, $this->time);
    }

    /** @return iterable<string, array{SpacecraftRumpCategoryEnum, bool}> */
    public static function categories(): iterable
    {
        foreach (SpacecraftRumpCategoryEnum::cases() as $category) {
            yield $category->name => [$category, in_array($category, [SpacecraftRumpCategoryEnum::ESCORT, SpacecraftRumpCategoryEnum::DESTROYER, SpacecraftRumpCategoryEnum::CRUISER], true)];
        }
    }

    #[DataProvider('categories')]
    public function testOnlyTheThreeCombatCategoriesCanBeSelectedAndInspectionDoesNotRollOrMutate(SpacecraftRumpCategoryEnum $category, bool $allowed): void
    {
        $this->target->getRump()->setCategoryId($category);
        if ($allowed) {
            $this->repository->shouldReceive('isAssimilateable')->once()->with($this->target->getRump())->andReturn(true);
        } else {
            $this->repository->shouldNotReceive('isAssimilateable');
        }
        $this->repository->shouldNotReceive('lock');
        $this->random->shouldNotReceive('rand', 'array_rand');
        $this->expectNoMutation();

        self::assertSame($allowed, $this->capture->canCaptureTarget($this->event, $this->source, $this->target));
    }

    public function testTheDatabaseWhitelistRemainsRequiredEvenForACruiser(): void
    {
        $this->repository->shouldReceive('isAssimilateable')->once()->with($this->target->getRump())->andReturn(false);
        $this->repository->shouldNotReceive('lock');
        $this->random->shouldNotReceive('rand', 'array_rand');
        $this->expectNoMutation();

        self::assertFalse($this->capture->hasTargets($this->event, $this->source));
    }

    /** @return iterable<string, array{?int, int, bool}> */
    public static function chances(): iterable
    {
        yield 'default succeeds at 33' => [null, 33, true];
        yield 'default fails at 34' => [null, 34, false];
        yield 'configured chance succeeds at its boundary' => [60, 60, true];
        yield 'configured chance fails beyond its boundary' => [60, 61, false];
        yield 'zero chance always fails' => [0, 1, false];
        yield 'one hundred percent always succeeds' => [100, 100, true];
    }

    #[DataProvider('chances')]
    public function testAutomaticCaptureRollsOnceAfterEligibilityAndDoesNotMutateOnFailure(?int $chance, int $roll, bool $success): void
    {
        if ($chance !== null) {
            $settings = $this->event->getConfiguration()->getSettings();
            $settings['assimilation_chance_percent'] = $chance;
            $this->event->getConfiguration()->setSettings($settings);
        }
        $this->repository->shouldReceive('lock')->once()->with($this->source);
        $this->repository->shouldReceive('isAssimilateable')->times($success ? 2 : 1)->with($this->target->getRump())->andReturn(true);
        $this->random->shouldReceive('rand')->once()->with(1, 100)->andReturn($roll);
        if ($success) {
            $this->random->shouldReceive('array_rand')->once()->andReturn(0);
            $this->expectCapture();
        } else {
            $this->random->shouldNotReceive('array_rand');
            $this->repository->shouldNotReceive('lock')->with($this->target);
            $this->expectNoMutation();
        }

        self::assertSame($success, $this->capture->tryCapture($this->event, $this->source));
        self::assertSame($success, $this->target->isBeingAssimilated());
        self::assertSame($success, $this->source->getSystemState(SpacecraftSystemTypeEnum::TRACTOR_BEAM));
        self::assertSame($success ? $this->target : null, $this->source->getTractoredShip());
    }

    public function testNoEligibleTargetsDoNotCauseAChanceRollOrRandomSelection(): void
    {
        $this->target->getRump()->setCategoryId(SpacecraftRumpCategoryEnum::FREIGHT);
        $this->repository->shouldReceive('lock')->once()->with($this->source);
        $this->repository->shouldNotReceive('isAssimilateable');
        $this->random->shouldNotReceive('rand', 'array_rand');
        $this->expectNoMutation();

        self::assertFalse($this->capture->tryCapture($this->event, $this->source));
    }

    public function testExplicitCaptureUsesTheChosenTargetWithoutAChanceRoll(): void
    {
        $settings = $this->event->getConfiguration()->getSettings();
        $settings['assimilation_chance_percent'] = 0;
        $this->event->getConfiguration()->setSettings($settings);
        $this->repository->shouldReceive('isAssimilateable')->twice()->with($this->target->getRump())->andReturn(true);
        $this->repository->shouldReceive('lock')->once()->with($this->source);
        $this->random->shouldNotReceive('rand', 'array_rand');
        $this->expectCapture();

        self::assertTrue($this->capture->captureTarget($this->event, $this->source, $this->target));
        self::assertSame($this->target, $this->source->getTractoredShip());
        self::assertTrue($this->target->isBeingAssimilated());
    }

    private function expectCapture(): void
    {
        $this->repository->shouldReceive('lock')->once()->with($this->target);
        $this->repository->shouldReceive('save')->once()->with(Mockery::type(SpacecraftAssimilated::class));
        $this->fleets->shouldReceive('leaveFleet')->once()->with($this->target)->andReturn(false);
        $this->messages->shouldReceive('send')->once();
        $this->time->shouldReceive('time')->once()->andReturn(1000);
    }

    private function expectNoMutation(): void
    {
        $this->repository->shouldNotReceive('save');
        $this->fleets->shouldNotReceive('leaveFleet');
        $this->messages->shouldNotReceive('send');
        $this->time->shouldNotReceive('time');
    }

    private function ship(int $id, StarSystemMap $location): Ship
    {
        $owner = $this->mock(User::class);
        $owner->shouldReceive('getId')->andReturn($id);
        $owner->shouldReceive('isVacationRequestOldEnough')->andReturn(false);
        $ship = new Ship();
        $ship->setUser($owner)->setLocation($location)->setRump(new SpacecraftRump()->setCategoryId(SpacecraftRumpCategoryEnum::CRUISER));
        $ship->setCondition(new SpacecraftCondition($ship));
        return $ship;
    }
}
