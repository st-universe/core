<?php

declare(strict_types=1);

namespace Stu\Lib\Damage;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Orm\Entity\ShipInterface;
use Stu\StuTestCase;

class DamageWrapperTest extends StuTestCase
{
    private DamageWrapper $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->subject = new DamageWrapper(0);
    }

    public static function provideGetDamageRelativeForDamageModeShieldsData(): array
    {
        return [
            //netDamage, isPhaser, modificator, targetShields, mode, expectedShieldDamage, leftNetDamage
            [100, true, 200, 49, DamageModeEnum::SHIELDS, 49, 75],
            [100, false,  0, 49, DamageModeEnum::SHIELDS, 49, 51],
            [10,  true, 400, 49, DamageModeEnum::SHIELDS, 40,  0],
            [30,  true, 200, 99, DamageModeEnum::SHIELDS, 60,  0],
            [20, false,   0, 49, DamageModeEnum::SHIELDS, 20,  0],
            [20, false,   0,  0, DamageModeEnum::SHIELDS, 0,  20]
        ];
    }

    #[DataProvider('provideGetDamageRelativeForDamageModeShieldsData')]
    public function testGetDamageRelativeForDamageModeShields(
        int $netDamage,
        bool $isPhaser,
        int $modificator,
        int $targetShields,
        DamageModeEnum $mode,
        int $expectedShieldDamage,
        int $leftNetDamage
    ): void {
        $target = $this->mock(ShipInterface::class);

        $target->shouldReceive('getShield')
            ->withNoArgs()
            ->andReturn($targetShields);

        $this->subject->setNetDamage($netDamage);
        $this->subject->setIsPhaserDamage($isPhaser);
        $this->subject->setModificator($modificator);

        $result = $this->subject->getDamageRelative($target, $mode);

        $this->assertEquals($expectedShieldDamage, $result);
        $this->assertEquals($leftNetDamage, $this->subject->getNetDamage());
    }

    public static function provideGetDamageRelativeForDamageModeHullData(): array
    {
        return [
            //netDamage, isTorpedo, modificator, mode, expectedHullDamage
            [100, true, 200, DamageModeEnum::HULL, 200],
            [100, false,  0, DamageModeEnum::HULL, 100],
            [10,  true, 400, DamageModeEnum::HULL,  40],
            [30,  true, 200, DamageModeEnum::HULL,  60],
            [20, false,   0, DamageModeEnum::HULL,  20]
        ];
    }

    #[DataProvider('provideGetDamageRelativeForDamageModeHullData')]
    public function testGetDamageRelativeForDamageModeHull(
        int $netDamage,
        bool $isTorpedo,
        int $modificator,
        DamageModeEnum $mode,
        int $expectedHullDamage
    ): void {
        $target = $this->mock(ShipInterface::class);

        $this->subject->setNetDamage($netDamage);
        $this->subject->setIsTorpedoDamage($isTorpedo);
        $this->subject->setModificator($modificator);

        $result = $this->subject->getDamageRelative($target, $mode);

        $this->assertEquals($expectedHullDamage, $result);
    }

    public function testCanDamageSystemExpectTrueIfNoWhitelistDefined(): void
    {
        $result = $this->subject->canDamageSystem(SpacecraftSystemTypeEnum::HULL);

        $this->assertTrue($result);
    }

    public function testCanDamageSystemExpectFalseIfNotOnWhitelist(): void
    {
        $this->subject->setTargetSystemTypes([SpacecraftSystemTypeEnum::SHIELDS]);

        $result = $this->subject->canDamageSystem(SpacecraftSystemTypeEnum::HULL);

        $this->assertFalse($result);
    }

    public function testCanDamageSystemExpectTrueIfOnWhitelist(): void
    {
        $this->subject->setTargetSystemTypes([
            SpacecraftSystemTypeEnum::SHIELDS,
            SpacecraftSystemTypeEnum::HULL
        ]);

        $result1 = $this->subject->canDamageSystem(SpacecraftSystemTypeEnum::HULL);
        $result2 = $this->subject->canDamageSystem(SpacecraftSystemTypeEnum::HULL);

        $this->assertTrue($result1);
        $this->assertTrue($result2);
    }
}
