<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Type;

use Stu\Component\Realtime\SpacecraftMovementPublisherInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\StuTestCase;

final class SensorCoverageSystemsTest extends StuTestCase
{
    public function testUplinkAndTachyonChangesInvalidateEverySharedCoverageSnapshot(): void
    {
        foreach ([UplinkShipSystem::class, TachyonScannerShipSystem::class] as $class) {
            $publisher = $this->mock(SpacecraftMovementPublisherInterface::class);
            $spacecraft = $this->mock(Spacecraft::class);
            $wrapper = $this->mock(SpacecraftWrapperInterface::class);
            $manager = $this->mock(SpacecraftSystemManagerInterface::class);
            $shipSystem = $this->mock(SpacecraftSystem::class);
            $system = new $class($publisher);
            $wrapper->shouldReceive('get')->andReturn($spacecraft);
            $spacecraft->shouldReceive('getSpacecraftSystem')->with($system->getSystemType())->twice()->andReturn($shipSystem);
            $shipSystem->shouldReceive('setMode')->with(SpacecraftSystemModeEnum::MODE_ON)->once();
            $shipSystem->shouldReceive('setMode')->with(SpacecraftSystemModeEnum::MODE_OFF)->once();
            $publisher->shouldReceive('publishCoverageChanged')->with($spacecraft)->times(3);
            $system->activate($wrapper, $manager);
            $system->deactivate($wrapper);
            $system->handleDestruction($wrapper);
        }
    }
}
