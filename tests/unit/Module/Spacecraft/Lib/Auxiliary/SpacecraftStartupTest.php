<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Auxiliary;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\MockInterface;
use Override;
use Stu\Component\Spacecraft\System\Control\ActivatorDeactivatorHelperInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\StuTestCase;

class SpacecraftStartupTest extends StuTestCase
{
    private MockInterface&SpacecraftRepositoryInterface $spacecraftRepository;
    private MockInterface&SpacecraftSystemManagerInterface $spacecraftSystemManager;
    private MockInterface&ActivatorDeactivatorHelperInterface $helper;

    private SpacecraftStartupInterface $subject;

    #[Override]
    public function setUp(): void
    {
        //injected
        $this->spacecraftRepository = $this->mock(SpacecraftRepositoryInterface::class);
        $this->spacecraftSystemManager = $this->mock(SpacecraftSystemManagerInterface::class);
        $this->helper = $this->mock(ActivatorDeactivatorHelperInterface::class);

        $this->subject = new SpacecraftStartup(
            $this->spacecraftRepository,
            $this->spacecraftSystemManager,
            $this->helper
        );
    }

    public function testStartupExpectNoSaveIfNoSystemActivated(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(Ship::class);
        $additionalSystemNbs = $this->mock(SpacecraftSystem::class);
        $systemTypeNbs = $this->mock(SpacecraftSystemTypeInterface::class);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);

        $ship->shouldReceive('getSystems')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([
                $additionalSystemNbs
            ]));

        $additionalSystemNbs->shouldReceive('getMode')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftSystemModeEnum::MODE_OFF);
        $additionalSystemNbs->shouldReceive('isHealthy')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $additionalSystemNbs->shouldReceive('getSystemType')
            ->withNoArgs()
            ->andReturn(SpacecraftSystemTypeEnum::NBS);
        $systemTypeNbs->shouldReceive('getDefaultMode')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftSystemModeEnum::MODE_OFF);
        $systemTypeNbs->shouldReceive('getSystemType')
            ->withNoArgs()
            ->andReturn(SpacecraftSystemTypeEnum::NBS);

        $this->spacecraftSystemManager->shouldReceive('lookupSystem')
            ->with(SpacecraftSystemTypeEnum::NBS)
            ->once()
            ->andReturn($systemTypeNbs);

        $this->helper->shouldReceive('activate')
            ->with($wrapper, SpacecraftSystemTypeEnum::NBS, Mockery::any())
            ->once()
            ->andReturn(false);

        $this->spacecraftRepository->shouldNotHaveBeenCalled();

        $this->subject->startup($wrapper, [
            SpacecraftSystemTypeEnum::NBS,
            SpacecraftSystemTypeEnum::PHASER,
            SpacecraftSystemTypeEnum::TORPEDO
        ]);
    }

    public function testStartupExpectSaveIfAnySystemActivated(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(Ship::class);
        $activeSystem = $this->mock(SpacecraftSystem::class);
        $destroyedSystem = $this->mock(SpacecraftSystem::class);
        $systemWithActiveDefault = $this->mock(SpacecraftSystem::class);
        $additionalSystemNbs = $this->mock(SpacecraftSystem::class);
        $additionalSystemPhaser = $this->mock(SpacecraftSystem::class);
        $systemTypeLifeSupport = $this->mock(SpacecraftSystemTypeInterface::class);
        $systemTypeNbs = $this->mock(SpacecraftSystemTypeInterface::class);
        $systemTypePhaser = $this->mock(SpacecraftSystemTypeInterface::class);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);

        $ship->shouldReceive('getSystems')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([
                $activeSystem,
                $destroyedSystem,
                $systemWithActiveDefault,
                $additionalSystemNbs,
                $additionalSystemPhaser
            ]));

        $activeSystem->shouldReceive('getMode')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftSystemModeEnum::MODE_ON);
        $destroyedSystem->shouldReceive('getMode')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftSystemModeEnum::MODE_OFF);
        $systemWithActiveDefault->shouldReceive('getMode')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftSystemModeEnum::MODE_OFF);
        $additionalSystemNbs->shouldReceive('getMode')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftSystemModeEnum::MODE_OFF);
        $additionalSystemPhaser->shouldReceive('getMode')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftSystemModeEnum::MODE_OFF);

        $destroyedSystem->shouldReceive('isHealthy')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $systemWithActiveDefault->shouldReceive('isHealthy')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $additionalSystemNbs->shouldReceive('isHealthy')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $additionalSystemPhaser->shouldReceive('isHealthy')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $systemWithActiveDefault->shouldReceive('getSystemType')
            ->withNoArgs()
            ->andReturn(SpacecraftSystemTypeEnum::LIFE_SUPPORT);
        $additionalSystemNbs->shouldReceive('getSystemType')
            ->withNoArgs()
            ->andReturn(SpacecraftSystemTypeEnum::NBS);
        $additionalSystemPhaser->shouldReceive('getSystemType')
            ->withNoArgs()
            ->andReturn(SpacecraftSystemTypeEnum::PHASER);

        $systemTypeLifeSupport->shouldReceive('getDefaultMode')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftSystemModeEnum::MODE_ALWAYS_ON);
        $systemTypeNbs->shouldReceive('getDefaultMode')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftSystemModeEnum::MODE_OFF);
        $systemTypePhaser->shouldReceive('getDefaultMode')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftSystemModeEnum::MODE_OFF);

        $systemTypeLifeSupport->shouldReceive('getSystemType')
            ->withNoArgs()
            ->andReturn(SpacecraftSystemTypeEnum::LIFE_SUPPORT);
        $systemTypeNbs->shouldReceive('getSystemType')
            ->withNoArgs()
            ->andReturn(SpacecraftSystemTypeEnum::NBS);
        $systemTypePhaser->shouldReceive('getSystemType')
            ->withNoArgs()
            ->andReturn(SpacecraftSystemTypeEnum::PHASER);

        $this->spacecraftSystemManager->shouldReceive('lookupSystem')
            ->with(SpacecraftSystemTypeEnum::LIFE_SUPPORT)
            ->once()
            ->andReturn($systemTypeLifeSupport);
        $this->spacecraftSystemManager->shouldReceive('lookupSystem')
            ->with(SpacecraftSystemTypeEnum::NBS)
            ->once()
            ->andReturn($systemTypeNbs);
        $this->spacecraftSystemManager->shouldReceive('lookupSystem')
            ->with(SpacecraftSystemTypeEnum::PHASER)
            ->once()
            ->andReturn($systemTypePhaser);

        $this->helper->shouldReceive('activate')
            ->with($wrapper, SpacecraftSystemTypeEnum::LIFE_SUPPORT, Mockery::any())
            ->once()
            ->andReturn(false);
        $this->helper->shouldReceive('activate')
            ->with($wrapper, SpacecraftSystemTypeEnum::NBS, Mockery::any())
            ->once()
            ->andReturn(false);
        $this->helper->shouldReceive('activate')
            ->with($wrapper, SpacecraftSystemTypeEnum::PHASER, Mockery::any())
            ->once()
            ->andReturn(true);

        $this->spacecraftRepository->shouldReceive('save')
            ->with($ship)
            ->once();

        $this->subject->startup($wrapper, [
            SpacecraftSystemTypeEnum::NBS,
            SpacecraftSystemTypeEnum::PHASER,
            SpacecraftSystemTypeEnum::TORPEDO
        ]);
    }
}
