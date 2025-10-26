<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleScreen\Addon;

use Mockery\MockInterface;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Lib\ModuleScreen\GradientColorInterface;
use Stu\Orm\Repository\TorpedoHullRepositoryInterface;
use Stu\Orm\Repository\WeaponShieldRepositoryInterface;
use Stu\StuTestCase;

class ModuleSelectorAddonFactoryTest extends StuTestCase
{
    private MockInterface&TorpedoHullRepositoryInterface $torpedoHullRepository;

    private MockInterface&WeaponShieldRepositoryInterface $weaponShieldRepository;

    private MockInterface&GradientColorInterface $gradientColor;

    private ModuleSelectorAddonFactory $subject;

    #[\Override]
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
        $result = $this->subject->createModuleSelectorAddon(SpacecraftModuleTypeEnum::EPS);

        $this->assertNull($result);
    }

    public function testCeateModuleSelectorAddonExpectAddonHullWhenTypeHull(): void
    {
        $result = $this->subject->createModuleSelectorAddon(SpacecraftModuleTypeEnum::HULL);

        $this->assertTrue($result instanceof ModuleSelectorAddonHull);
    }

    public function testCeateModuleSelectorAddonExpectAddonShieldWhenTypeShield(): void
    {
        $result = $this->subject->createModuleSelectorAddon(SpacecraftModuleTypeEnum::SHIELDS);

        $this->assertTrue($result instanceof ModuleSelectorAddonShield);
    }
}
