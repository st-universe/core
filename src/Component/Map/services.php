<?php

declare(strict_types=1);

namespace Stu\Component\Map;

use function DI\autowire;

return [
    EncodedMapInterface::class => autowire(EncodedMap::class),
];
