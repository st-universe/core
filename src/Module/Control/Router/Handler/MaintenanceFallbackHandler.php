<?php

declare(strict_types=1);

namespace Stu\Module\Control\Router\Handler;

use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\Router\FallbackRouteException;

class MaintenanceFallbackHandler implements FallbackHandlerInterface
{
    #[\Override]
    public function handle(FallbackRouteException $e, ViewControllerContext $context): void
    {
        $context->setPageTitle('Wartungsmodus');
        $context->setTemplateFile('html/index/maintenance.twig');
    }
}
