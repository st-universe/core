<?php

declare(strict_types=1);

namespace Stu\Component\Realtime;

use function DI\autowire;

return [
    RealtimeRedisFactory::class => autowire(RealtimeRedisFactory::class),
    StarmapRealtimeTokenFactory::class => autowire(StarmapRealtimeTokenFactory::class),
    SpacecraftMovementPublisherInterface::class => autowire(SpacecraftMovementPublisher::class),
];
