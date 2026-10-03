<?php

declare(strict_types=1);

namespace Stu\Module\Control\Router\Handler;

use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\Router\FallbackRouteException;

class ResetFallbackHandler implements FallbackHandlerInterface
{
    #[\Override]
    public function handle(FallbackRouteException $e, ViewControllerContext $context): void
    {
        $context->setPageTitle('Resetmodus');
        $context->setTemplateFile('html/index/gameReset.twig');
    }
}
