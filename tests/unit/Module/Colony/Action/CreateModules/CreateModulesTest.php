<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\CreateModules;

use Doctrine\Common\Collections\ArrayCollection;
use request;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyChangeable;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\ModuleBuildingFunction;
use Stu\Orm\Entity\ModuleCost;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ModuleBuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\ModuleQueueRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\StuTestCase;

class CreateModulesTest extends StuTestCase
{
    public function testHandleProjectsMissingCommoditiesAcrossMultipleFailedModules(): void
    {
        $colonyId = 42;
        $userId = 7;
        $commodityId = 1;
        $function = BuildingFunctionEnum::MODULEFAB_TYPE1_LVL3;
        $informationWrapper = new InformationWrapper();

        request::setMockVars([
            'id' => $colonyId,
            'func' => $function->value,
            'moduleids' => [101, 202],
            'values' => [1, 1]
        ]);

        $colonyLoader = $this->mock(ColonyLoaderInterface::class);
        $moduleBuildingFunctionRepository = $this->mock(ModuleBuildingFunctionRepositoryInterface::class);
        $moduleQueueRepository = $this->mock(ModuleQueueRepositoryInterface::class);
        $planetFieldRepository = $this->mock(PlanetFieldRepositoryInterface::class);
        $storageManager = $this->mock(StorageManagerInterface::class);
        $colonyRepository = $this->mock(ColonyRepositoryInterface::class);
        $game = $this->mock(GameControllerInterface::class);
        $user = $this->mock(User::class);
        $colony = $this->mock(Colony::class);
        $changeable = $this->mock(ColonyChangeable::class);
        $storedMaterial = $this->mock(Storage::class);
        $material = $this->mock(Commodity::class);
        $moduleBuildingFunction1 = $this->mock(ModuleBuildingFunction::class);
        $moduleBuildingFunction2 = $this->mock(ModuleBuildingFunction::class);
        $module1 = $this->mock(Module::class);
        $module2 = $this->mock(Module::class);
        $cost1 = $this->mock(ModuleCost::class);
        $cost2 = $this->mock(ModuleCost::class);

        $game->shouldReceive('setView')
            ->with(ShowColony::VIEW_IDENTIFIER)
            ->once();
        $game->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $game->shouldReceive('getInfo')
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
        $colony->shouldReceive('getStorage')
            ->withNoArgs()
            ->andReturn(new ArrayCollection([$commodityId => $storedMaterial]));
        $colony->shouldReceive('getChangeable')
            ->withNoArgs()
            ->andReturn($changeable);

        $changeable->shouldReceive('getEps')
            ->withNoArgs()
            ->andReturn(999);
        $changeable->shouldNotReceive('lowerEps');

        $storedMaterial->shouldReceive('getCommodityId')
            ->withNoArgs()
            ->andReturn($commodityId);
        $storedMaterial->shouldReceive('getAmount')
            ->withNoArgs()
            ->andReturn(10);

        $material->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($commodityId);
        $material->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('Baumaterial');

        $planetFieldRepository->shouldReceive('getCountByColonyAndBuildingFunctionAndState')
            ->with($colony, [$function], [0, 1])
            ->once()
            ->andReturn(1);

        $moduleBuildingFunctionRepository->shouldReceive('getByBuildingFunctionAndUser')
            ->with($function, $userId)
            ->once()
            ->andReturn([$moduleBuildingFunction1, $moduleBuildingFunction2]);

        $moduleBuildingFunction1->shouldReceive('getModuleId')
            ->withNoArgs()
            ->andReturn(101);
        $moduleBuildingFunction1->shouldReceive('getModule')
            ->withNoArgs()
            ->andReturn($module1);
        $moduleBuildingFunction2->shouldReceive('getModuleId')
            ->withNoArgs()
            ->andReturn(202);
        $moduleBuildingFunction2->shouldReceive('getModule')
            ->withNoArgs()
            ->andReturn($module2);

        $module1->shouldReceive('getEcost')
            ->withNoArgs()
            ->andReturn(0);
        $module1->shouldReceive('getCost')
            ->withNoArgs()
            ->andReturn(new ArrayCollection([$cost1]));
        $module1->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('Klingonische Torpedobank (Klasse 6)');
        $module2->shouldReceive('getEcost')
            ->withNoArgs()
            ->andReturn(0);
        $module2->shouldReceive('getCost')
            ->withNoArgs()
            ->andReturn(new ArrayCollection([$cost2]));
        $module2->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('Mehrfach-Torpedogeschütz (Klasse 6)');

        $cost1->shouldReceive('getCommodity')
            ->withNoArgs()
            ->andReturn($material);
        $cost1->shouldReceive('getAmount')
            ->withNoArgs()
            ->andReturn(120);
        $cost2->shouldReceive('getCommodity')
            ->withNoArgs()
            ->andReturn($material);
        $cost2->shouldReceive('getAmount')
            ->withNoArgs()
            ->andReturn(110);

        $storageManager->shouldNotReceive('lowerStorage');
        $colonyRepository->shouldNotReceive('save');
        $moduleQueueRepository->shouldNotReceive('getByColonyAndModuleAndBuilding');
        $moduleQueueRepository->shouldNotReceive('prototype');
        $moduleQueueRepository->shouldNotReceive('save');

        $subject = new CreateModules(
            $colonyLoader,
            $moduleBuildingFunctionRepository,
            $moduleQueueRepository,
            $planetFieldRepository,
            $storageManager,
            $colonyRepository
        );

        $subject->handle($game);

        $this->assertSame(
            [
                '0 von 1 Klingonische Torpedobank (Klasse 6)',
                '<div style="padding-left: 20px;">- Zur Herstellung von weiteren 1 Klingonische Torpedobank (Klasse 6) werden benötigt: 110 Baumaterial</div>',
                '0 von 1 Mehrfach-Torpedogeschütz (Klasse 6)',
                '<div style="padding-left: 20px;">- Zur Herstellung von weiteren 1 Mehrfach-Torpedogeschütz (Klasse 6) werden benötigt: 110 Baumaterial</div>',
                '<div style="padding-left: 20px;">- Insgesamt werden zusätzlich benötigt: 220 Baumaterial</div>'
            ],
            $informationWrapper->getInformations()
        );
    }
}
