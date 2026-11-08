<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Creation;

use Mockery\MockInterface;
use Monolog\Logger;
use Monolog\Handler\TestHandler;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Component\Spacecraft\System\Data\EnergyWeaponSystemData;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Logging\StuLogger;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Template\StatusBarFactoryInterface;
use Stu\Orm\Entity\BuildplanModule;
use Stu\Orm\Entity\ConstructionProgress;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\SpacecraftRumpBaseValues;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\Station;
use Stu\Orm\Repository\ConstructionProgressRepositoryInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
use Stu\Orm\Repository\SpacecraftSystemRepositoryInterface;
use Stu\StuTestCase;

final class SpacecraftCorrectorTest extends StuTestCase
{
    private MockInterface&SpacecraftBuildplanRepositoryInterface $plans;
    private MockInterface&ConstructionProgressRepositoryInterface $progress;
    private MockInterface&SpacecraftWrapperFactoryInterface $wrappers;
    private SpacecraftCorrector $corrector;
    private TestHandler $logs;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->plans = $this->mock(SpacecraftBuildplanRepositoryInterface::class);
        $this->progress = $this->mock(ConstructionProgressRepositoryInterface::class);
        $this->wrappers = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $this->corrector = new SpacecraftCorrector($this->plans, $this->progress, $this->wrappers);
        $this->logs = new TestHandler();
        StuLogger::setMock(new Logger('corrector-test', [$this->logs]));
    }

    /** @return iterable<string, array{int, int, int}> */
    public static function qualities(): iterable
    {
        yield 'legacy standard stays standard' => [0, 0, 100];
        yield 'existing quality is preserved' => [33, 33, 103];
        yield 'missing installed metadata is repaired from the saved plan' => [17, 0, 102];
        yield 'fractional metadata is repaired without integer stat changes' => [1, 0, 100];
    }

    #[DataProvider('qualities')]
    public function testCorrectionUsesSavedQualityAndPreservesInstalledSystemState(int $planGrade, int $installedGrade, int $expectedDamage): void
    {
        [$plan, $ship, $system, $data] = $this->fixture(new Ship(), $planGrade, $installedGrade);
        $foreignModule = $this->module(2, SpacecraftModuleTypeEnum::COMPUTER);
        $unplanned = new SpacecraftSystem()->setModule($foreignModule)->setSystemType(SpacecraftSystemTypeEnum::COMPUTER)
            ->setImprovementSteps(70)->setStatus(31)->setMode(SpacecraftSystemModeEnum::MODE_OFF)->setData('Zusätzliches System');
        $ship->getSystems()->set(SpacecraftSystemTypeEnum::COMPUTER->value, $unplanned);
        $plan->getSpacecraftList()->add($ship);
        $this->plans->shouldReceive('getAllNonNpcBuildplans')->once()->andReturn([$plan]);
        $this->progress->shouldReceive('findAll')->once()->andReturn([]);

        $this->corrector->correctAllSpacecrafts();

        self::assertSame($expectedDamage, $data->baseDamage);
        self::assertSame($planGrade, $system->getImprovementSteps());
        $module = $system->getModule();
        self::assertNotNull($module);
        self::assertSame($planGrade, $plan->getModuleImprovementSteps($module));
        self::assertSame(55, $system->getStatus());
        self::assertSame(SpacecraftSystemModeEnum::MODE_ON, $system->getMode());
        self::assertSame(PHP_INT_MAX, $system->getCooldown());
        self::assertSame($system, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::PHASER));
        self::assertSame($plan, $ship->getBuildplan());
        self::assertSame(70, $unplanned->getImprovementSteps());
        self::assertSame(31, $unplanned->getStatus());
        self::assertSame('Zusätzliches System', $unplanned->getData());
        self::assertCount($planGrade === $installedGrade ? 0 : 2, $this->logs->getRecords());
    }

    public function testCompletedStationsUseTheirSavedQualityAndUnfinishedStationsAreSkipped(): void
    {
        [$plan, $station, $system, $data] = $this->fixture(new Station(), 17, 0);
        self::assertInstanceOf(Station::class, $station);
        $unfinished = new Station();
        $unfinished->setBuildplan($plan);
        $this->plans->shouldReceive('getAllNonNpcBuildplans')->once()->andReturn([]);
        $this->progress->shouldReceive('findAll')->once()->andReturn([
            new ConstructionProgress()->setStation($station)->setRemainingTicks(0),
            new ConstructionProgress()->setStation($unfinished)->setRemainingTicks(2),
            new ConstructionProgress()->setStation(new Station())->setRemainingTicks(0),
        ]);

        $this->corrector->correctAllSpacecrafts();

        self::assertSame(102, $data->baseDamage);
        self::assertSame(17, $system->getImprovementSteps());
        self::assertSame(55, $system->getStatus());
        self::assertSame(PHP_INT_MAX, $system->getCooldown());
        self::assertSame($plan, $station->getBuildplan());
    }

    public function testEmptyBuildplansDoNotCreateNewShipsOrSystems(): void
    {
        $this->plans->shouldReceive('getAllNonNpcBuildplans')->once()->andReturn([new SpacecraftBuildplan()]);
        $this->progress->shouldReceive('findAll')->once()->andReturn([]);
        $this->wrappers->shouldNotReceive('wrapSpacecraft');

        $this->corrector->correctAllSpacecrafts();
    }

    /** @return array{SpacecraftBuildplan, Spacecraft, SpacecraftSystem, EnergyWeaponSystemData} */
    private function fixture(Spacecraft $ship, int $planGrade, int $installedGrade): array
    {
        new ReflectionProperty(Spacecraft::class, 'id')->setValue($ship, 101);
        $rump = $this->mock(SpacecraftRump::class);
        $rump->shouldReceive('getBaseValues')->andReturn(new SpacecraftRumpBaseValues()->setModuleLevel(1)->setBaseDamage(100));
        $ship->setRump($rump);
        $module = $this->module(1, SpacecraftModuleTypeEnum::PHASER);
        $plan = new SpacecraftBuildplan()->setRump($rump);
        $plan->getModules()->set(1, new BuildplanModule()->setModule($module)->setModuleType($module->getType())->setImprovementSteps($planGrade));
        $ship->setBuildplan($plan);
        $system = new SpacecraftSystem()->setModule($module)->setSystemType(SpacecraftSystemTypeEnum::PHASER)
            ->setImprovementSteps($installedGrade)->setStatus(55)->setMode(SpacecraftSystemModeEnum::MODE_ON)->setCooldown(PHP_INT_MAX);
        $ship->getSystems()->set(SpacecraftSystemTypeEnum::PHASER->value, $system);
        $systems = $this->mock(SpacecraftSystemRepositoryInterface::class);
        $systems->shouldReceive('save')->with($system)->once();
        $data = new EnergyWeaponSystemData($systems, $this->mock(StatusBarFactoryInterface::class));
        $data->setSpacecraft($ship);
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $wrapper->shouldReceive('__toString')->twice()->andReturn('Systemwerte');
        $wrapper->shouldReceive('getEnergyWeaponSystemData')->once()->andReturn($data);
        $this->wrappers->shouldReceive('wrapSpacecraft')->with($ship)->once()->andReturn($wrapper);
        return [$plan, $ship, $system, $data];
    }

    private function module(int $id, SpacecraftModuleTypeEnum $type): Module
    {
        $module = new Module()->setType($type)->setLevel(1)->setName('Testmodul');
        new ReflectionProperty(Module::class, 'id')->setValue($module, $id);
        return $module;
    }
}
