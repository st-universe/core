<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\Module;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Transfer\EntityWithStorageInterface;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\StuRandom;
use Stu\Orm\Entity\BuildplanModuleInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\SpacecraftBuildplanInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\SpacecraftSystemInterface;
use Stu\StuTestCase;

class ModuleRecyclingTest extends StuTestCase
{
    /** @var MockInterface&StorageManagerInterface */
    private $storageManager;
    /** @var MockInterface&StuRandom */
    private $stuRandom;

    /** @var MockInterface&SpacecraftInterface */
    private $spacecraft;
    /** @var MockInterface&EntityWithStorageInterface */
    private $entity;
    /** @var MockInterface&InformationInterface */
    private $information;

    private ModuleRecyclingInterface $subject;

    #[Override]
    public function setUp(): void
    {
        $this->storageManager = $this->mock(StorageManagerInterface::class);
        $this->stuRandom = $this->mock(StuRandom::class);

        $this->spacecraft = $this->mock(SpacecraftInterface::class);
        $this->entity = $this->mock(EntityWithStorageInterface::class);
        $this->information = $this->mock(InformationInterface::class);

        $this->subject = new ModuleRecycling(
            $this->storageManager,
            $this->stuRandom
        );
    }

    public function testRetrieveSomeModulesExpectNothingWhenNoBuildplan(): void
    {
        $this->spacecraft->shouldReceive('getBuildplan')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->subject->retrieveSomeModules($this->spacecraft, $this->entity, $this->information);
    }

    public function testRetrieveSomeModulesExpectRecyclingOfInstalledSystems(): void
    {
        $buildplan = $this->mock(SpacecraftBuildplanInterface::class);
        $systemHealth100 = $this->mock(SpacecraftSystemInterface::class);
        $systemHealth50 = $this->mock(SpacecraftSystemInterface::class);
        $systemHealth1 = $this->mock(SpacecraftSystemInterface::class);
        $moduleHealth100 = $this->mock(ModuleInterface::class);
        $moduleHealth50 = $this->mock(ModuleInterface::class);
        $moduleHealth1 = $this->mock(ModuleInterface::class);
        $commodityHealth100 = $this->mock(CommodityInterface::class);
        $commodityHealth50 = $this->mock(CommodityInterface::class);
        $buildplanModuleHealth50 = $this->mock(BuildplanModuleInterface::class);

        $this->spacecraft->shouldReceive('getBuildplan')
            ->withNoArgs()
            ->once()
            ->andReturn($buildplan);
        $this->spacecraft->shouldReceive('getSystems')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$systemHealth100, $systemHealth50, $systemHealth1]));

        $systemHealth100->shouldReceive('getModule')
            ->withNoArgs()
            ->once()
            ->andReturn($moduleHealth100);
        $systemHealth50->shouldReceive('getModule')
            ->withNoArgs()
            ->once()
            ->andReturn($moduleHealth50);
        $systemHealth1->shouldReceive('getModule')
            ->withNoArgs()
            ->once()
            ->andReturn($moduleHealth1);

        $systemHealth100->shouldReceive('getStatus')
            ->withNoArgs()
            ->once()
            ->andReturn(100);
        $systemHealth50->shouldReceive('getStatus')
            ->withNoArgs()
            ->once()
            ->andReturn(50);
        $systemHealth1->shouldReceive('getStatus')
            ->withNoArgs()
            ->once()
            ->andReturn(1);

        $moduleHealth100->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(1001);
        $moduleHealth50->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(1002);
        $moduleHealth1->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(1003);

        $moduleHealth100->shouldReceive('getCommodity')
            ->withNoArgs()
            ->once()
            ->andReturn($commodityHealth100);
        $moduleHealth50->shouldReceive('getCommodity')
            ->withNoArgs()
            ->once()
            ->andReturn($commodityHealth50);

        $moduleHealth100->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('MODULE_100');
        $moduleHealth50->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('MODULE_50');

        $buildplanModuleHealth50->shouldReceive('getModuleCount')
            ->withNoArgs()
            ->once()
            ->andReturn(7);
        $buildplanModuleHealth50->shouldReceive('getModule')
            ->withNoArgs()
            ->once()
            ->andReturn($moduleHealth50);

        $buildplan->shouldReceive('getModules')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([1002 => $buildplanModuleHealth50]));

        $this->stuRandom->shouldReceive('rand')
            ->with(1, 100)
            ->times(3)
            ->andReturn(50, 25, 2);

        $this->entity->shouldReceive('getMaxStorage')
            ->withNoArgs()
            ->once()
            ->andReturn(100);
        $this->entity->shouldReceive('getStorageSum')
            ->withNoArgs()
            ->andReturn(99);

        $this->storageManager->shouldReceive('upperStorage')
            ->with($this->entity, $commodityHealth100, 1)
            ->once();
        $this->storageManager->shouldReceive('upperStorage')
            ->with($this->entity, $commodityHealth50, 7)
            ->once();

        $this->information->shouldReceive('addInformationf')
            ->with('Folgendes Modul konnte recycelt werden: %s, Anzahl: %d', 'MODULE_100', 1)
            ->once();
        $this->information->shouldReceive('addInformationf')
            ->with('Folgendes Modul konnte recycelt werden: %s, Anzahl: %d', 'MODULE_50', 7)
            ->once();

        $this->subject->retrieveSomeModules($this->spacecraft, $this->entity, $this->information);
    }

    public function testRetrieveSomeModulesExpectRecyclingOfBuildplanModules(): void
    {
        $buildplan = $this->mock(SpacecraftBuildplanInterface::class);
        $moduleCount1 = $this->mock(ModuleInterface::class);
        $moduleCount11 = $this->mock(ModuleInterface::class);
        $commodityCount1 = $this->mock(CommodityInterface::class);
        $commodityCount11 = $this->mock(CommodityInterface::class);
        $buildplanModuleCount1 = $this->mock(BuildplanModuleInterface::class);
        $buildplanModuleCount11 = $this->mock(BuildplanModuleInterface::class);

        $this->spacecraft->shouldReceive('getBuildplan')
            ->withNoArgs()
            ->once()
            ->andReturn($buildplan);
        $this->spacecraft->shouldReceive('getSystems')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection());

        $moduleCount1->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(1001);
        $moduleCount11->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(1002);

        $moduleCount1->shouldReceive('getCommodity')
            ->withNoArgs()
            ->once()
            ->andReturn($commodityCount1);
        $moduleCount11->shouldReceive('getCommodity')
            ->withNoArgs()
            ->once()
            ->andReturn($commodityCount11);

        $moduleCount1->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('MODULE_1');
        $moduleCount11->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('MODULE_11');

        $buildplanModuleCount1->shouldReceive('getModuleCount')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $buildplanModuleCount11->shouldReceive('getModuleCount')
            ->withNoArgs()
            ->once()
            ->andReturn(11);

        $buildplanModuleCount1->shouldReceive('getModule')
            ->withNoArgs()
            ->once()
            ->andReturn($moduleCount1);
        $buildplanModuleCount11->shouldReceive('getModule')
            ->withNoArgs()
            ->once()
            ->andReturn($moduleCount11);

        $buildplan->shouldReceive('getModules')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$buildplanModuleCount1, $buildplanModuleCount11]));

        $this->stuRandom->shouldReceive('rand')
            ->with(1, 100)
            ->times(2)
            ->andReturn(12);
        $this->stuRandom->shouldReceive('rand')
            ->with(1, 1, true, 1)
            ->once()
            ->andReturn(1);
        $this->stuRandom->shouldReceive('rand')
            ->with(1, 11, true, 6)
            ->once()
            ->andReturn(7);

        $this->entity->shouldReceive('getMaxStorage')
            ->withNoArgs()
            ->once()
            ->andReturn(100);
        $this->entity->shouldReceive('getStorageSum')
            ->withNoArgs()
            ->andReturn(99);

        $this->storageManager->shouldReceive('upperStorage')
            ->with($this->entity, $commodityCount1, 1)
            ->once();
        $this->storageManager->shouldReceive('upperStorage')
            ->with($this->entity, $commodityCount11, 7)
            ->once();

        $this->information->shouldReceive('addInformationf')
            ->with('Folgendes Modul konnte recycelt werden: %s, Anzahl: %d', 'MODULE_1', 1)
            ->once();
        $this->information->shouldReceive('addInformationf')
            ->with('Folgendes Modul konnte recycelt werden: %s, Anzahl: %d', 'MODULE_11', 7)
            ->once();

        $this->subject->retrieveSomeModules($this->spacecraft, $this->entity, $this->information, 25);
    }

    public function testRetrieveSomeModulesExpectNoRecyclingIfStorageIsFull(): void
    {
        $buildplan = $this->mock(SpacecraftBuildplanInterface::class);
        $module = $this->mock(ModuleInterface::class);
        $buildplanModule = $this->mock(BuildplanModuleInterface::class);

        $this->spacecraft->shouldReceive('getBuildplan')
            ->withNoArgs()
            ->once()
            ->andReturn($buildplan);
        $this->spacecraft->shouldReceive('getSystems')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection());

        $module->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(1001);

        $buildplanModule->shouldReceive('getModuleCount')
            ->withNoArgs()
            ->once()
            ->andReturn(1);

        $buildplanModule->shouldReceive('getModule')
            ->withNoArgs()
            ->once()
            ->andReturn($module);

        $buildplan->shouldReceive('getModules')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$buildplanModule]));

        $this->stuRandom->shouldReceive('rand')
            ->with(1, 1, true, 1)
            ->once()
            ->andReturn(1);

        $this->entity->shouldReceive('getMaxStorage')
            ->withNoArgs()
            ->once()
            ->andReturn(100);
        $this->entity->shouldReceive('getStorageSum')
            ->withNoArgs()
            ->andReturn(100);

        $this->information->shouldReceive('addInformation')
            ->with('Kein Lagerraum frei um Module zu recyclen!')
            ->once();

        $this->subject->retrieveSomeModules($this->spacecraft, $this->entity, $this->information);
    }
}
