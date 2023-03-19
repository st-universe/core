<?php

declare(strict_types=1);

namespace Stu\Component\Index;

use Stu\Component\Index\News\NewsFactory;
use Stu\Component\Index\News\NewsFactoryInterface;

use function DI\autowire;

return [
    NewsFactoryInterface::class => autowire(NewsFactory::class)
];
