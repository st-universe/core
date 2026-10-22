<?php

declare(strict_types=1);

namespace Stu\Component\Realtime;

use JBBCode\Parser;
use Noodlehaus\Config;
use Stu\Module\Config\StuConfigInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\StuTestCase;

final class SpacecraftMovementPublisherTest extends StuTestCase
{
    public function testDeletingAnAlreadyInvisibleSpacecraftDoesNotReadOrPublishItsPosition(): void
    {
        $spacecraft = $this->mock(Spacecraft::class);
        $spacecraft->shouldReceive('isRpgModuleInvisible')->once()->andReturn(true);
        $spacecraft->shouldNotReceive('getLocation');
        $publisher = new SpacecraftMovementPublisher(
            new RealtimeRedisFactory($this->mock(StuConfigInterface::class)),
            new Parser(),
            new RealtimeChannels(new Config([]))
        );
        $publisher->publishRemoval($spacecraft);
    }
}
