<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Creation;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Orm\Entity\BuildplanModule;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Repository\SpacecraftSystemRepositoryInterface;
use Stu\StuTestCase;

final class SpacecraftSystemCreationQualityTest extends StuTestCase
{
    /** @return iterable<array{int}> */
    public static function grades(): iterable
    {
        yield [0];
        yield [17];
        yield [70];
    }

    #[DataProvider('grades')]
    public function testNewSystemsCopyTheSavedPlanGradeAndAuxiliarySystemsStayStandard(int $grade): void
    {
        $ship = new Ship();
        $rump = $this->mock(SpacecraftRump::class);
        $rump->shouldReceive('getCategoryId')->andReturn(SpacecraftRumpCategoryEnum::FRIGATE);
        $rump->shouldReceive('isShipyard')->once()->andReturn(false);
        $rump->shouldReceive('getRoleId')->once()->andReturnNull();
        $ship->setRump($rump);
        $module = new Module()->setType(SpacecraftModuleTypeEnum::PHASER);
        new ReflectionProperty(Module::class, 'id')->setValue($module, 101);
        $entry = new BuildplanModule()->setModule($module)->setModuleType($module->getType())->setImprovementSteps($grade);
        $systems = $this->mock(SpacecraftSystemRepositoryInterface::class);
        $systems->shouldReceive('prototype')->times(4)->andReturnUsing(fn (): SpacecraftSystem => new SpacecraftSystem());
        $systems->shouldReceive('save')->times(4);

        new SpacecraftSystemCreation($systems)->createShipSystemsByModuleList($ship, new ArrayCollection([$entry]), null);

        self::assertCount(4, $ship->getSystems());
        $weapon = $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::PHASER);
        self::assertSame($module, $weapon->getModule());
        self::assertSame($grade, $weapon->getImprovementSteps());
        self::assertSame(100, $weapon->getStatus());
        self::assertSame(SpacecraftSystemModeEnum::MODE_OFF, $weapon->getMode());
        self::assertSame(0, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::DEFLECTOR)->getImprovementSteps());
    }
}
