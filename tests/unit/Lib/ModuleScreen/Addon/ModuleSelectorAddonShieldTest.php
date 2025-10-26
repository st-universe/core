<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleScreen\Addon;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Stu\Lib\ModuleScreen\GradientColorInterface;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\WeaponShield;
use Stu\Orm\Repository\WeaponShieldRepositoryInterface;
use Stu\StuTestCase;

class ModuleSelectorAddonShieldTest extends StuTestCase
{
    private MockInterface&WeaponShieldRepositoryInterface $weaponShieldRepository;

    private MockInterface&GradientColorInterface $gradientColor;

    private ModuleSelectorAddonShield $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->weaponShieldRepository = $this->mock(WeaponShieldRepositoryInterface::class);
        $this->gradientColor = $this->mock(GradientColorInterface::class);

        $this->subject = new ModuleSelectorAddonShield(
            $this->weaponShieldRepository,
            $this->gradientColor
        );
    }

    public function testgetModificators(): void
    {
        $shieldModule = $this->mock(Module::class);

        $weaponShield_1 = $this->mock(WeaponShield::class);
        $weaponShield_2 = $this->mock(WeaponShield::class);
        $weaponShield_3 = $this->mock(WeaponShield::class);
        $weaponShield_4 = $this->mock(WeaponShield::class);

        $weaponShields = new ArrayCollection([
            $weaponShield_1,
            $weaponShield_2,
            $weaponShield_3,
            $weaponShield_4
        ]);

        $shieldModule->shouldReceive('getWeaponShield')
            ->withNoArgs()
            ->andReturn($weaponShields);

        $weaponShield_1->shouldReceive('getFactionId')
            ->withNoArgs()
            ->andReturn(null);
        $weaponShield_2->shouldReceive('getFactionId')
            ->withNoArgs()
            ->andReturn(1);
        $weaponShield_3->shouldReceive('getFactionId')
            ->withNoArgs()
            ->andReturn(1);
        $weaponShield_4->shouldReceive('getFactionId')
            ->withNoArgs()
            ->andReturn(2);

        $weaponShield_2->shouldReceive('getModificator')
            ->withNoArgs()
            ->andReturn(1);
        $weaponShield_3->shouldReceive('getModificator')
            ->withNoArgs()
            ->andReturn(5);
        $weaponShield_4->shouldReceive('getModificator')
            ->withNoArgs()
            ->andReturn(42);

        $this->weaponShieldRepository->shouldReceive('getModificatorMinAndMax')
            ->withNoArgs()
            ->once()
            ->andReturn([222, 444]);

        $this->gradientColor->shouldReceive('calculateGradientColor')
            ->with(3, 222, 444)
            ->andReturn('colorA');
        $this->gradientColor->shouldReceive('calculateGradientColor')
            ->with(42, 222, 444)
            ->andReturn('colorB');

        $this->subject->getModificators($shieldModule);
        $result = $this->subject->getModificators($shieldModule);

        $this->assertEquals(2, count($result));
        $this->assertEquals('colorA', $result[0]['gradientColor']);
        $this->assertEquals(1, $result[0]['factionId']);
        $this->assertEquals(2, $result[1]['factionId']);
        $this->assertEquals('colorB', $result[1]['gradientColor']);
    }
}
