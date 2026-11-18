<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\BuildShip;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use request;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Spacecraft\Buildplan\BuildplanSignatureCreationInterface;
use Stu\Component\Spacecraft\Crew\SpacecraftCrewCalculatorInterface;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\ModuleScreen\ModuleSelector;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Colony\View\ShowModuleScreenBuildplan\ShowModuleScreenBuildplan;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\ViewContextMetadataTypeEnum;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyChangeable;
use Stu\Orm\Entity\ColonyShipQueue;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\ShipRumpBuildingFunction;
use Stu\Orm\Entity\ShipRumpModuleLevel;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\SpacecraftRumpBaseValues;
use Stu\Orm\Entity\Storage;
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

class BuildShipTest extends StuTestCase
{
    public static function npcGiftBuildplans(): iterable
    {
        yield 'exhausted' => [0, 'npc-signature'];
        yield 'buildable' => [1, 'npc-signature'];
        yield 'modified' => [1, 'modified-signature'];
    }

    #[DataProvider('npcGiftBuildplans')]
    public function testHandleUsesNpcGiftCrewForSignature(int $remainingCount, string $submittedSignature): void
    {
        $userId = 123;
        $colonyId = 456;
        $rumpId = 789;
        $planId = 101;
        $moduleId = 202;
        $buildingFunction = BuildingFunctionEnum::FRIGATE_SHIPYARD;

        request::setMockVars([
            'id' => $colonyId,
            'rumpid' => $rumpId,
            'planid' => $planId,
            sprintf('mod_%d', SpacecraftModuleTypeEnum::HULL->value) => [$moduleId]
        ]);

        $colonyFunctionManager = $this->mock(ColonyFunctionManagerInterface::class);
        $shipRumpModuleLevelRepository = $this->mock(ShipRumpModuleLevelRepositoryInterface::class);
        $colonyLoader = $this->mock(ColonyLoaderInterface::class);
        $buildplanModuleRepository = $this->mock(BuildplanModuleRepositoryInterface::class);
        $shipRumpBuildingFunctionRepository = $this->mock(ShipRumpBuildingFunctionRepositoryInterface::class);
        $spacecraftBuildplanRepository = $this->mock(SpacecraftBuildplanRepositoryInterface::class);
        $moduleRepository = $this->mock(ModuleRepositoryInterface::class);
        $colonyShipQueueRepository = $this->mock(ColonyShipQueueRepositoryInterface::class);
        $spacecraftRumpRepository = $this->mock(SpacecraftRumpRepositoryInterface::class);
        $storageManager = $this->mock(StorageManagerInterface::class);
        $colonyLibFactory = $this->mock(ColonyLibFactoryInterface::class);
        $shipCrewCalculator = $this->mock(SpacecraftCrewCalculatorInterface::class);
        $buildplanSignatureCreation = $this->mock(BuildplanSignatureCreationInterface::class);
        $colonyRepository = $this->mock(ColonyRepositoryInterface::class);

        $context = $this->mock(ActionControllerContext::class);
        $user = $this->mock(User::class);
        $colony = $this->mock(Colony::class);
        $changeable = $this->mock(ColonyChangeable::class);
        $rump = $this->mock(SpacecraftRump::class);
        $baseValues = $this->mock(SpacecraftRumpBaseValues::class);
        $buildingFunctionMapping = $this->mock(ShipRumpBuildingFunction::class);
        $moduleLevels = $this->mock(ShipRumpModuleLevel::class);
        $module = $this->mock(Module::class);
        $module->shouldReceive('getId')->andReturn($moduleId);
        $plan = $this->mock(SpacecraftBuildplan::class);
        $plan->shouldReceive('getModuleImprovementSteps')->with($module)->andReturn(0);
        $informationWrapper = new InformationWrapper();

        $context->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $context->shouldReceive('setView')
            ->with(ShowModuleScreenBuildplan::VIEW_IDENTIFIER)
            ->once();
        $context->shouldReceive('setViewContext')
            ->with(ViewContextMetadataTypeEnum::BUILDPLAN, $planId)
            ->once();
        $context->shouldReceive('getInfo')
            ->withNoArgs()
            ->andReturn($informationWrapper);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($userId);

        $colonyLoader->shouldReceive('loadWithOwnerValidation')
            ->with($colonyId, $userId)
            ->once()
            ->andReturn($colony);

        $colony->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($colonyId);
        $colony->shouldReceive('getChangeable')
            ->withNoArgs()
            ->andReturn($changeable);
        $colony->shouldReceive('isBlocked')
            ->withNoArgs()
            ->andReturn(false);

        $changeable->shouldReceive('getEps')
            ->withNoArgs()
            ->andReturn(100);

        $spacecraftRumpRepository->shouldReceive('find')
            ->with($rumpId)
            ->once()
            ->andReturn($rump);

        $rump->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($rumpId);
        $rump->shouldReceive('getEpsCost')
            ->withNoArgs()
            ->andReturn(10);
        $rump->shouldReceive('getBaseValues')
            ->withNoArgs()
            ->andReturn($baseValues);

        $baseValues->shouldReceive('getSpecialSlots')
            ->withNoArgs()
            ->andReturn(0);

        $shipRumpBuildingFunctionRepository->shouldReceive('getByShipRump')
            ->with($rump)
            ->once()
            ->andReturn([$buildingFunctionMapping]);
        $buildingFunctionMapping->shouldReceive('getBuildingFunction')
            ->withNoArgs()
            ->andReturn($buildingFunction);
        $colonyFunctionManager->shouldReceive('hasActiveFunction')
            ->with($colony, $buildingFunction)
            ->once()
            ->andReturn(true);

        $colonyShipQueueRepository->shouldReceive('getAmountByColonyAndBuildingFunctionAndMode')
            ->with($colonyId, $buildingFunction, Mockery::any())
            ->twice()
            ->andReturn(0);

        $shipRumpModuleLevelRepository->shouldReceive('getByShipRump')
            ->with($rump)
            ->once()
            ->andReturn($moduleLevels);
        $moduleLevels->shouldReceive('isMandatory')
            ->withAnyArgs()
            ->andReturn(false);

        $moduleRepository->shouldReceive('find')
            ->with($moduleId)
            ->once()
            ->andReturn($module);

        $shipCrewCalculator->shouldNotReceive('getCrewUsage');
        $shipCrewCalculator->shouldReceive('getMaxCrewCountByRump')
            ->with($rump)
            ->once()
            ->andReturn(10);

        $spacecraftBuildplanRepository->shouldReceive('find')
            ->with($planId)
            ->once()
            ->andReturn($plan);
        $spacecraftBuildplanRepository->shouldNotReceive('getByUserShipRumpAndSignature');

        $plan->shouldReceive('getUserId')
            ->withNoArgs()
            ->andReturn($userId);
        $plan->shouldReceive('getRumpId')
            ->withNoArgs()
            ->andReturn($rumpId);
        $plan->shouldReceive('getNpcGift')
            ->withNoArgs()
            ->andReturn(true);
        $plan->shouldReceive('getCrew')
            ->withNoArgs()
            ->once()
            ->andReturn(7);
        $plan->shouldReceive('getSignature')
            ->withNoArgs()
            ->once()
            ->andReturn('npc-signature');

        $queue = new ColonyShipQueue();
        if ($remainingCount > 0 && $submittedSignature === 'npc-signature') {
            $commodity = new Commodity();
            $module->shouldReceive('getCommodityId')->andReturn($moduleId);
            $module->shouldReceive('getCommodity')->andReturn($commodity);
            $module->shouldReceive('getType')->andReturn(SpacecraftModuleTypeEnum::HULL);
            $colony->shouldReceive('getStorage')->andReturn(new ArrayCollection([
                $moduleId => new Storage()->setAmount(1)
            ]));
            $selector = $this->mock(ModuleSelector::class);
            $selector->shouldReceive('getAvailableModules')->once()->andReturn([$moduleId => true]);
            $colonyLibFactory->shouldReceive('createModuleSelector')
                ->with(SpacecraftModuleTypeEnum::HULL, $colony, $rump, $user, $plan, true)
                ->once()->andReturn($selector);
            $storageManager->shouldReceive('removeStorage')->with($colony, $commodity, 1, 0)->once();
            $context->shouldReceive('setView')->with(ShowColony::VIEW_IDENTIFIER)->once();
            $plan->shouldReceive('getName')->andReturn('NPC-Geschenk');
            $plan->shouldReceive('getBuildtime')->andReturn(24000);
            $plan->shouldReceive('setCount')->with(0)->once();
            $rump->shouldReceive('getName')->andReturn('Sovereign');
            $colonyShipQueueRepository->shouldReceive('prototype')->once()->andReturn($queue);
            $changeable->shouldReceive('lowerEps')->with(10)->once();
            $colonyRepository->shouldReceive('save')->with($colony)->once();
            $colonyShipQueueRepository->shouldReceive('save')->with($queue)->once();
        } else {
            $storageManager->shouldNotReceive('removeStorage');
            $colonyShipQueueRepository->shouldNotReceive('save');
        }
        $plan->shouldReceive('getCount')
            ->withNoArgs()
            ->andReturn($remainingCount);

        $buildplanSignatureCreation->shouldReceive('createSignature')
            ->with(
                Mockery::on(fn (array $modules): bool => $modules[$moduleId] === $module),
                7
            )
            ->once()
            ->andReturn($submittedSignature);

        $subject = new BuildShip(
            $colonyFunctionManager,
            $shipRumpModuleLevelRepository,
            $colonyLoader,
            $buildplanModuleRepository,
            $shipRumpBuildingFunctionRepository,
            $spacecraftBuildplanRepository,
            $moduleRepository,
            $colonyShipQueueRepository,
            $spacecraftRumpRepository,
            $storageManager,
            $colonyLibFactory,
            $shipCrewCalculator,
            $buildplanSignatureCreation,
            $colonyRepository
        );

        $subject->handle($context);

        if ($submittedSignature !== 'npc-signature') {
            self::assertSame(['Du kannst diesen Bauplan nicht ändern'], $informationWrapper->getInformations());
        } elseif ($remainingCount === 0) {
            self::assertSame(['Dieser Bauplan ist nicht mehr baubar'], $informationWrapper->getInformations());
        } else {
            self::assertSame($plan, $queue->getSpacecraftBuildplan());
            self::assertSame(24000, $queue->getBuildtime());
        }
    }
}
