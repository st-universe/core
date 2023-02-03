<?php

declare(strict_types=1);

namespace Stu\Component\Crew;

use Stu\Component\GrapViz\GraphVizFactory;
use Stu\Component\GrapViz\GraphVizFactoryInterface;
use function DI\autowire;

return [
    GraphVizFactoryInterface::class => autowire(GraphVizFactory::class),
];
