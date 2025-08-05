<?php

namespace Stu\Module\Control\Router\Handler;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\Router\FallbackRouteException;

class UplinkFallbackHandler implements FallbackHandlerInterface
{
    public function handle(FallbackRouteException $e, GameControllerInterface $game): void
    {
        $game->getInfo()->addInformation('Diese Aktion ist per Uplink nicht möglich!');

        if (request::isAjaxRequest() && !request::has('switch')) {
            $game->setMacroInAjaxWindow('html/systeminformation.twig');
        } else {
            $game->setViewTemplate('html/empty.twig');
        }
    }
}
