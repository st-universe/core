<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleScreen\Addon;

use Mockery\MockInterface;
use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Lib\ModuleScreen\GradientColorInterface;
use Stu\Orm\Repository\TorpedoHullRepositoryInterface;
use Stu\Orm\Repository\WeaponShieldRepositoryInterface;
use Stu\StuTestCase;

class ModuleSelectorAddonFactoryTest extends StuTestCase
{
    /** @var MockInterface&TorpedoHullRepositoryInterface */
    private MockInterface $torpedoHullRepository;

    /** @var MockInterface&WeaponShieldRepositoryInterface */
    private MockInterface $weaponShieldRepository;

    /** @var MockInterface&GradientColorInterface */
    private MockInterface $gradientColor;

    private ModuleSelectorAddonFactory $subject;

    protected function setUp(): void
    {
        $this->torpedoHullRepository = $this->mock(TorpedoHullRepositoryInterface::class);
        $this->weaponShieldRepository = $this->mock(WeaponShieldRepositoryInterface::class);
        $this->gradientColor = $this->mock(GradientColorInterface::class);

        $this->subject = new ModuleSelectorAddonFactory(
            $this->torpedoHullRepository,
            $this->weaponShieldRepository,
            $this->gradientColor
        );
    }

    public function testCeateModuleSelectorAddonExpectNullWhenTypeUnknown(): void
    {
        $result = $this->subject->createModuleSelectorAddon(ShipModuleTypeEnum::EPS);

        $this->assertNull($result);
    }

    public function testCeateModuleSelectorAddonExpectAddonHullWhenTypeHull(): void
    {
        $result = $this->subject->createModuleSelectorAddon(ShipModuleTypeEnum::HULL);

        $this->assertTrue($result instanceof ModuleSelectorAddonHull);
    }

    public function testCeateModuleSelectorAddonExpectAddonShieldWhenTypeShield(): void
    {
        $result = $this->subject->createModuleSelectorAddon(ShipModuleTypeEnum::SHIELDS);

        $this->assertTrue($result instanceof ModuleSelectorAddonShield);
    }
}
