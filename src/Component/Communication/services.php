<?php

declare(strict_types=1);

namespace Stu\Component\Communication;

use Stu\Component\Communication\Kn\KnBbCodeParser;
use Stu\Component\Communication\Kn\KnFactory;
use Stu\Component\Communication\Kn\KnFactoryInterface;

use function DI\autowire;

return [
    KnBbCodeParser::class => autowire(KnBbCodeParser::class),
    KnFactoryInterface::class => autowire(KnFactory::class)
];
