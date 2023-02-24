<?php

declare(strict_types=1);

namespace Stu\Component\Image;

use function DI\autowire;

return [
    ImageCreationInterface::class => autowire(ImageCreation::class),
];
