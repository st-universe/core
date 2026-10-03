<?php

declare(strict_types=1);

namespace Stu\Module\Template;

use function DI\autowire;

return [
    TemplateInterface::class => autowire(Template::class),
    TemplateHelperInterface::class => autowire(TemplateHelper::class),
    StatusBarFactoryInterface::class => autowire(StatusBarFactory::class)
];
