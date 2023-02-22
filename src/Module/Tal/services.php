<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Stu\Module\Tal\TalComponentFactory;
use Stu\Module\Tal\TalComponentFactoryInterface;

use function DI\autowire;

return [
    TalComponentFactoryInterface::class => autowire(TalComponentFactory::class),
];
