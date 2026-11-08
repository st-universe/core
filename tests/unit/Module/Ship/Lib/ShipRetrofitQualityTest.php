<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Component\Spacecraft\System\Data\EnergyWeaponSystemData;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\BuildplanModule;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\SpacecraftRumpBaseValues;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Repository\SpacecraftSystemRepositoryInterface;
use Stu\StuTestCase;

final class ShipRetrofitQualityTest extends StuTestCase
{
    private MockInterface&SpacecraftSystemRepositoryInterface $systems;
    private MockInterface&StorageManagerInterface $storage;
    private MockInterface&StuRandom $random;
    private MockInterface&ShipWrapperInterface $wrapper;
    private Colony $colony;
    private Ship $ship;
    private ShipRetrofit $retrofit;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->systems = $this->mock(SpacecraftSystemRepositoryInterface::class);
        $this->storage = $this->mock(StorageManagerInterface::class);
        $this->random = $this->mock(StuRandom::class);
        $factory = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $messages = $this->mock(PrivateMessageSenderInterface::class);
        $this->retrofit = new ShipRetrofit($this->systems, $factory, $this->storage, $messages, $this->random);
        $this->ship = new Ship();
        $this->ship->setName('Testschiff')->setMaxHull(100)->setCondition(new SpacecraftCondition($this->ship)->setHull(50));
        $rump = $this->mock(SpacecraftRump::class);
        $rump->shouldReceive('getBaseValues')->andReturn(new SpacecraftRumpBaseValues()->setModuleLevel(1)->setBaseDamage(100)->setBaseHull(100));
        $this->ship->setRump($rump);
        $this->colony = $this->mock(Colony::class);
        $this->colony->shouldReceive('getName')->andReturn('Testkolonie');
        $this->colony->shouldReceive('getUserId')->andReturn(42);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->wrapper->shouldReceive('get')->andReturn($this->ship);
        $factory->shouldReceive('wrapShip')->with($this->ship)->once()->andReturn($this->wrapper);
        $messages->shouldReceive('send')->once();
    }

    /** @return iterable<string, array{int, int, int, int, bool}> */
    public static function changes(): iterable
    {
        yield 'standard to improved with recovered standard module' => [0, 17, 100, 25, true];
        yield 'same module with another positive quality' => [1, 17, 100, 25, true];
        yield 'quality downgrade without recovery at roll 26' => [17, 0, 100, 26, false];
        yield 'damaged module cannot be recovered' => [17, 18, 99, 25, false];
    }

    #[DataProvider('changes')]
    public function testSameModuleWithChangedQualityIsReplacedAndOtherSystemsArePreserved(int $oldQuality, int $newQuality, int $oldHealth, int $roll, bool $recovered): void
    {
        $weapon = $this->module(1, SpacecraftModuleTypeEnum::PHASER);
        $computer = $this->module(2, SpacecraftModuleTypeEnum::COMPUTER);
        $oldPlan = $this->plan([[$weapon, $oldQuality], [$computer, 17]]);
        $newPlan = $this->plan([[$weapon, $newQuality], [$computer, 17]]);
        $this->ship->setBuildplan($oldPlan);
        $oldSystem = $this->install($weapon, $oldQuality)->setStatus($oldHealth);
        $unchanged = $this->install($computer, 17)->setStatus(73)->setData('Unveränderte Systemdaten');
        $newSystem = new SpacecraftSystem();
        $this->systems->shouldReceive('prototype')->once()->andReturn($newSystem);
        $this->systems->shouldReceive('delete')->with($oldSystem)->once();
        $this->systems->shouldReceive('save')->with($newSystem)->once();
        $data = $this->mock(EnergyWeaponSystemData::class);
        $this->wrapper->shouldReceive('getEnergyWeaponSystemData')->once()->andReturn($data);
        $data->shouldReceive('setBaseDamage')->with((int) round(100 + $newQuality / 10))->once()->andReturnSelf();
        $data->shouldReceive('update')->once();
        if ($oldHealth === 100) {
            $this->random->shouldReceive('rand')->with(1, 100)->once()->andReturn($roll);
        }
        if ($recovered) {
            $this->storage->shouldReceive('addStorage')->with($this->colony, $weapon->getCommodity(), [$oldQuality => 1])->once();
        }

        $this->retrofit->updateBy($this->ship, $newPlan, $this->colony);

        self::assertSame($newPlan, $this->ship->getBuildplan());
        self::assertSame($newSystem, $this->ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::PHASER));
        self::assertSame($weapon, $newSystem->getModule());
        self::assertSame($newQuality, $newSystem->getImprovementSteps());
        self::assertSame(100, $newSystem->getStatus());
        self::assertSame(SpacecraftSystemModeEnum::MODE_OFF, $newSystem->getMode());
        self::assertSame($unchanged, $this->ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::COMPUTER));
        self::assertSame(17, $unchanged->getImprovementSteps());
        self::assertSame(73, $unchanged->getStatus());
        self::assertSame('Unveränderte Systemdaten', $unchanged->getData());
    }

    public function testUnchangedModuleAndQualityAreNotReplacedOrRecalculated(): void
    {
        $weapon = $this->module(1, SpacecraftModuleTypeEnum::PHASER);
        $this->ship->setBuildplan($this->plan([[$weapon, 17]]));
        $system = $this->install($weapon, 17)->setStatus(73)->setData('Bestehende Daten');
        $newPlan = $this->plan([[$weapon, 17]]);

        $this->retrofit->updateBy($this->ship, $newPlan, $this->colony);

        self::assertSame($system, $this->ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::PHASER));
        self::assertSame(17, $system->getImprovementSteps());
        self::assertSame(73, $system->getStatus());
        self::assertSame('Bestehende Daten', $system->getData());
        self::assertSame($newPlan, $this->ship->getBuildplan());
    }

    public function testHullReplacementAppliesNewValueWithoutReplacingOtherShipSystems(): void
    {
        $oldHull = $this->module(1, SpacecraftModuleTypeEnum::HULL);
        $newHull = $this->module(2, SpacecraftModuleTypeEnum::HULL)->setLevel(2)->setUpgradeFactor(20);
        $weapon = $this->module(3, SpacecraftModuleTypeEnum::PHASER);
        $this->ship->setBuildplan($this->plan([[$oldHull, 0], [$weapon, 17]]));
        $unchanged = $this->install($weapon, 17)->setStatus(73)->setData('Bestehende Daten');
        $newPlan = $this->plan([[$newHull, 0], [$weapon, 17]]);

        $this->retrofit->updateBy($this->ship, $newPlan, $this->colony);

        self::assertSame(120, $this->ship->getMaxHull());
        self::assertSame(60, $this->ship->getCondition()->getHull());
        self::assertSame($unchanged, $this->ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::PHASER));
        self::assertSame('Bestehende Daten', $unchanged->getData());
        self::assertSame($newPlan, $this->ship->getBuildplan());
    }

    private function module(int $id, SpacecraftModuleTypeEnum $type): Module
    {
        $module = new Module()->setName('Testmodul')->setType($type)->setLevel(1);
        new ReflectionProperty(Module::class, 'id')->setValue($module, $id);
        new ReflectionProperty(Module::class, 'commodity')->setValue($module, new Commodity());
        return $module;
    }

    /** @param list<array{Module, int}> $modules */
    private function plan(array $modules): SpacecraftBuildplan
    {
        $plan = new SpacecraftBuildplan();
        foreach ($modules as [$module, $quality]) {
            $plan->getModules()->set($module->getId(), new BuildplanModule()->setModule($module)->setModuleType($module->getType())->setImprovementSteps($quality));
        }
        return $plan;
    }

    private function install(Module $module, int $quality): SpacecraftSystem
    {
        $system = new SpacecraftSystem()->setModule($module)->setSystemType($module->getType()->getSystemType())
            ->setImprovementSteps($quality)->setStatus(100)->setMode(SpacecraftSystemModeEnum::MODE_ON);
        $this->ship->getSystems()->set($system->getSystemType()->value, $system);
        return $system;
    }
}
