<?php

declare(strict_types=1);

namespace Stu\Module\Control\Router\Handler;

use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\Router\FallbackRouteException;

class SpacecraftNotFoundFallbackHandler implements FallbackHandlerInterface
{
    #[\Override]
    public function handle(FallbackRouteException $e, ViewControllerContext $context): void
    {
        $context->getInfo()->addInformation($e->getMessage());
        $context->setViewTemplate('html/empty.twig');
    }
}
