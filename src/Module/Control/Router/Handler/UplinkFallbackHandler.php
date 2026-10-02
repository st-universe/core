<?php

declare(strict_types=1);

namespace Stu\Module\Control\Router\Handler;

use request;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\Router\FallbackRouteException;

class UplinkFallbackHandler implements FallbackHandlerInterface
{
    #[\Override]
    public function handle(FallbackRouteException $e, ViewControllerContext $context): void
    {
        $context->getInfo()->addInformation('Diese Aktion ist per Uplink nicht möglich!');

        if (request::isAjaxRequest() && !request::has('switch')) {
            $context->setMacroInAjaxWindow('html/systeminformation.twig');
        } else {
            $context->setViewTemplate('html/empty.twig');
        }
    }
}
