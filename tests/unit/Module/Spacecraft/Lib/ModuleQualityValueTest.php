<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\SpacecraftRumpBaseValues;
use Stu\StuTestCase;

final class ModuleQualityValueTest extends StuTestCase
{
    /** @return iterable<string, array{int, int, int, int}> */
    public static function profiles(): iterable
    {
        yield 'downgrade improves by seven percentage points' => [2, 1, 70, 87];
        yield 'default improves by seven percentage points' => [1, 1, 70, 107];
        yield 'upgrade improves by seven percentage points' => [1, 2, 70, 127];
        yield 'fractional improvement stays integer' => [1, 1, 1, 100];
        yield 'half value rounds upward' => [1, 1, 5, 101];
    }

    #[DataProvider('profiles')]
    public function testQualityAddsPercentagePointsAndRoundsAtFinalValue(int $rumpLevel, int $moduleLevel, int $steps, int $expected): void
    {
        $rump = $this->mock(SpacecraftRump::class);
        $baseValues = $this->mock(SpacecraftRumpBaseValues::class);
        $rump->shouldReceive('getBaseValues')->andReturn($baseValues);
        $baseValues->shouldReceive('getModuleLevel')->andReturn($rumpLevel);
        $module = new Module()->setType(SpacecraftModuleTypeEnum::PHASER)
            ->setLevel($moduleLevel)->setDowngradeFactor(20)->setDefaultFactor(0)->setUpgradeFactor(20);

        self::assertSame($expected, new ModuleValueCalculator()->calculateModuleValue($rump, $module, 100, $steps));
        self::assertSame(20, $module->getDowngradeFactor());
        self::assertSame(0, $module->getDefaultFactor());
        self::assertSame(20, $module->getUpgradeFactor());
    }
}
