<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\RetrofitShip;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use request;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Spacecraft\Buildplan\BuildplanSignatureCreationInterface;
use Stu\Component\Spacecraft\Crew\SpacecraftCrewCalculatorInterface;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Exception\AccessViolationException;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyChangeable;
use Stu\Orm\Entity\ColonyShipQueue;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\ShipRumpBuildingFunction;
use Stu\Orm\Entity\ShipRumpModuleLevel;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\BuildplanModuleRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\Orm\Repository\ShipRumpBuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\ShipRumpModuleLevelRepositoryInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;
use Stu\StuTestCase;

final class RetrofitShipTest extends StuTestCase
{
    private MockInterface&ColonyFunctionManagerInterface $functions;
    private MockInterface&ShipRumpModuleLevelRepositoryInterface $moduleLevels;
    private MockInterface&ColonyLoaderInterface $colonyLoader;
    private MockInterface&ShipRumpBuildingFunctionRepositoryInterface $rumpFunctions;
    private MockInterface&SpacecraftBuildplanRepositoryInterface $plans;
    private MockInterface&ColonyShipQueueRepositoryInterface $queues;
    private MockInterface&SpacecraftRumpRepositoryInterface $rumps;
    private MockInterface&SpacecraftCrewCalculatorInterface $crewCalculator;
    private MockInterface&ColonyRepositoryInterface $colonies;
    private MockInterface&ShipLoaderInterface $ships;
    private MockInterface&BuildplanSignatureCreationInterface $signatures;
    private MockInterface&ActionControllerContext $context;
    private MockInterface&Colony $colony;
    private MockInterface&Ship $ship;
    private MockInterface&SpacecraftRump $rump;
    private MockInterface&ShipWrapperInterface $wrapper;
    private MockInterface&User $user;
    private RetrofitShip $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->functions = $this->mock(ColonyFunctionManagerInterface::class);
        $this->moduleLevels = $this->mock(ShipRumpModuleLevelRepositoryInterface::class);
        $this->colonyLoader = $this->mock(ColonyLoaderInterface::class);
        $this->rumpFunctions = $this->mock(ShipRumpBuildingFunctionRepositoryInterface::class);
        $this->plans = $this->mock(SpacecraftBuildplanRepositoryInterface::class);
        $this->queues = $this->mock(ColonyShipQueueRepositoryInterface::class);
        $this->rumps = $this->mock(SpacecraftRumpRepositoryInterface::class);
        $this->crewCalculator = $this->mock(SpacecraftCrewCalculatorInterface::class);
        $this->colonies = $this->mock(ColonyRepositoryInterface::class);
        $this->ships = $this->mock(ShipLoaderInterface::class);
        $this->signatures = $this->mock(BuildplanSignatureCreationInterface::class);
        $this->subject = new RetrofitShip(
            $this->functions,
            $this->moduleLevels,
            $this->colonyLoader,
            $this->mock(BuildplanModuleRepositoryInterface::class),
            $this->rumpFunctions,
            $this->plans,
            $this->mock(ModuleRepositoryInterface::class),
            $this->queues,
            $this->rumps,
            $this->mock(StorageManagerInterface::class),
            $this->mock(ColonyLibFactoryInterface::class),
            $this->crewCalculator,
            $this->colonies,
            $this->ships,
            $this->signatures
        );
        request::setMockVars(['id' => 4, 'shipid' => 5, 'rumpid' => 6]);
        $this->context = $this->mock(ActionControllerContext::class);
        $this->user = $this->mock(User::class);
        $this->user->shouldReceive('getId')->andReturn(42);
        $this->context->shouldReceive('getUser')->andReturn($this->user);
        $this->colony = $this->mock(Colony::class);
        $this->colonyLoader->shouldReceive('loadWithOwnerValidation')->with(4, 42)->once()->andReturn($this->colony);
        $this->colony->shouldReceive('getId')->andReturn(4);
        $this->rump = $this->mock(SpacecraftRump::class);
        $this->rumps->shouldReceive('find')->with(6)->once()->andReturn($this->rump);
        $this->rump->shouldReceive('getId')->andReturn(6);
        $this->ship = $this->mock(Ship::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
    }

    public function testRejectsForeignShipThroughOwnerBoundLoader(): void
    {
        $this->ships->shouldReceive('getWrapperByIdAndUser')->with(5, 42)->once()->andThrow(AccessViolationException::class);
        $this->expectException(AccessViolationException::class);

        $this->subject->handle($this->context);
    }

    #[DataProvider('invalidConditions')]
    public function testRejectsIneligibleShipBeforeConsumingResources(string $condition): void
    {
        $this->prepareShip($condition);
        if ($condition === 'foreignPlan') {
            $oldPlan = new SpacecraftBuildplan();
            $foreignUser = $this->mock(User::class);
            $foreignUser->shouldReceive('getId')->andReturn(99);
            $oldPlan->setUser($foreignUser);
            $this->ship->shouldReceive('getBuildplan')->once()->andReturn($oldPlan);
            $this->context->shouldReceive('getInfo')->once()->andReturn(new InformationWrapper());
        }

        $this->subject->handle($this->context);
    }

    public static function invalidConditions(): iterable
    {
        foreach (['location', 'rump', 'state', 'eligibility', 'warp', 'foreignPlan'] as $condition) {
            yield [$condition];
        }
    }

    public function testQueuesEligibleOwnedShipAtOwnColony(): void
    {
        $this->prepareShip();
        $oldPlan = new SpacecraftBuildplan()->setUser($this->user)->setRump($this->rump)->setName('Alter Plan');
        $this->ship->shouldReceive('getBuildplan')->once()->andReturn($oldPlan);
        $function = new ShipRumpBuildingFunction()->setBuildingFunction(BuildingFunctionEnum::BASE_CAMP);
        $this->rumpFunctions->shouldReceive('getByShipRump')->with($this->rump)->once()->andReturn([$function]);
        $this->functions->shouldReceive('hasActiveFunction')->with($this->colony, BuildingFunctionEnum::BASE_CAMP)->once()->andReturn(true);
        $this->context->shouldReceive('setView')->twice();
        $this->queues->shouldReceive('getAmountByColonyAndBuildingFunctionAndMode')->with(4, BuildingFunctionEnum::BASE_CAMP, 1)->once()->andReturn(0);
        $this->queues->shouldReceive('getAmountByColonyAndBuildingFunctionAndMode')->with(4, BuildingFunctionEnum::BASE_CAMP, 2)->once()->andReturn(0);
        $changeable = new ColonyChangeable($this->colony);
        $changeable->setEps(100);
        $this->colony->shouldReceive('getChangeable')->once()->andReturn($changeable);
        $this->rump->shouldReceive('getEpsCost')->andReturn(20);
        $this->colony->shouldReceive('isBlocked')->once()->andReturn(false);
        $levels = $this->mock(ShipRumpModuleLevel::class);
        $this->moduleLevels->shouldReceive('getByShipRump')->with($this->rump)->once()->andReturn($levels);
        $levels->shouldReceive('isMandatory')->andReturn(false);
        $this->rump->shouldReceive('getBaseValues->getSpecialSlots')->andReturn(0);
        $this->crewCalculator->shouldReceive('getCrewUsage')->with([], $this->rump, $this->user)->once()->andReturn(0);
        $this->crewCalculator->shouldReceive('getMaxCrewCountByRump')->with($this->rump)->once()->andReturn(10);
        $this->colony->shouldReceive('getStorage')->once()->andReturn(new ArrayCollection());
        $this->signatures->shouldReceive('createSignature')->with([], 0)->once()->andReturn('signature');
        $plan = new SpacecraftBuildplan()->setUser($this->user)->setRump($this->rump)->setName('Neuer Plan')->setBuildtime(30);
        $this->plans->shouldReceive('getByUserShipRumpAndSignature')->with(42, 6, 'signature')->once()->andReturn($plan);
        $queue = new ColonyShipQueue();
        $this->queues->shouldReceive('prototype')->once()->andReturn($queue);
        $this->queues->shouldReceive('save')->with($queue)->once();
        $this->colonies->shouldReceive('save')->with($this->colony)->once();
        $condition = new SpacecraftCondition($this->ship);
        $this->ship->shouldReceive('getCondition')->once()->andReturn($condition);
        $this->ship->shouldReceive('getName')->once()->andReturn('Testschiff');
        $this->context->shouldReceive('getInfo')->twice()->andReturn(new InformationWrapper());

        $this->subject->handle($this->context);

        self::assertSame($this->ship, $queue->getShip());
        self::assertSame($this->colony, $queue->getColony());
        self::assertSame(42, $queue->getUserId());
        self::assertSame($plan, $queue->getSpacecraftBuildplan());
        self::assertSame(SpacecraftStateEnum::RETROFIT, $condition->getState());
        self::assertSame(80, $changeable->getEps());
    }

    private function prepareShip(string $condition = ''): void
    {
        $this->ships->shouldReceive('getWrapperByIdAndUser')->with(5, 42)->once()->andReturn($this->wrapper);
        $this->wrapper->shouldReceive('get')->once()->andReturn($this->ship);
        $location = new StarSystemMap();
        $this->colony->shouldReceive('getLocation')->andReturn($location);
        $this->ship->shouldReceive('getLocation')->andReturn($condition === 'location' ? new StarSystemMap() : $location);
        $shipRump = $this->mock(SpacecraftRump::class);
        $shipRump->shouldReceive('getId')->andReturn($condition === 'rump' ? 99 : 6);
        $this->ship->shouldReceive('getRump')->andReturn($shipRump);
        $this->ship->shouldReceive('getState')->andReturn($condition === 'state' ? SpacecraftStateEnum::RETROFIT : SpacecraftStateEnum::NONE);
        $this->wrapper->shouldReceive('canBeRetrofitted')->andReturn($condition !== 'eligibility');
        $this->ship->shouldReceive('isWarped')->andReturn($condition === 'warp');
    }
}
