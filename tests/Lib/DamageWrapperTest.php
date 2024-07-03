<?php

declare(strict_types=1);

namespace Stu\Lib;

use Stu\Component\Ship\ShipEnum;
use Stu\Orm\Entity\ShipInterface;
use Stu\StuTestCase;

class DamageWrapperTest extends StuTestCase
{
    private DamageWrapper $subject;

    protected function setUp(): void
    {
        $this->subject = new DamageWrapper(0);
    }

    public static function provideGetDamageRelativeForDamageModeShieldsData(): array
    {
        return [
            //netDamage, isPhaser, modificator, targetShields, mode, expectedShieldDamage, leftNetDamage
            [100, true, 200, 49, ShipEnum::DAMAGE_MODE_SHIELDS, 49, 75],
            [100, false,  0, 49, ShipEnum::DAMAGE_MODE_SHIELDS, 49, 51],
            [10,  true, 400, 49, ShipEnum::DAMAGE_MODE_SHIELDS, 40,  0],
            [30,  true, 200, 99, ShipEnum::DAMAGE_MODE_SHIELDS, 60,  0],
            [20, false,   0, 49, ShipEnum::DAMAGE_MODE_SHIELDS, 20,  0],
            [20, false,   0,  0, ShipEnum::DAMAGE_MODE_SHIELDS, 0,  20]
        ];
    }

    /**
     * @dataProvider provideGetDamageRelativeForDamageModeShieldsData
     */
    public function testGetDamageRelativeForDamageModeShields(
        int $netDamage,
        bool $isPhaser,
        int $modificator,
        int $targetShields,
        int $mode,
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
            [100, true, 200, ShipEnum::DAMAGE_MODE_HULL, 200],
            [100, false,  0, ShipEnum::DAMAGE_MODE_HULL, 100],
            [10,  true, 400, ShipEnum::DAMAGE_MODE_HULL,  40],
            [30,  true, 200, ShipEnum::DAMAGE_MODE_HULL,  60],
            [20, false,   0, ShipEnum::DAMAGE_MODE_HULL,  20]
        ];
    }

    /**
     * @dataProvider provideGetDamageRelativeForDamageModeHullData
     */
    public function testGetDamageRelativeForDamageModeHull(
        int $netDamage,
        bool $isTorpedo,
        int $modificator,
        int $mode,
        int $expectedHullDamage
    ): void {
        $target = $this->mock(ShipInterface::class);

        $this->subject->setNetDamage($netDamage);
        $this->subject->setIsTorpedoDamage($isTorpedo);
        $this->subject->setModificator($modificator);

        $result = $this->subject->getDamageRelative($target, $mode);

        $this->assertEquals($expectedHullDamage, $result);
    }
}
