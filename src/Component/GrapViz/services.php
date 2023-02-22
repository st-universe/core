<?php

declare(strict_types=1);

namespace Stu\Component\GrapViz;

use function DI\autowire;

return [
    GraphVizFactoryInterface::class => autowire(GraphVizFactory::class),
];
