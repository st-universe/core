<?php

namespace Stu\Module\Control\Router\Handler;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\Router\FallbackRouteException;

class SpacecraftNotFoundFallbackHandler implements FallbackHandlerInterface
{
    public function handle(FallbackRouteException $e, GameControllerInterface $game): void
    {
        $game->addInformation($e->getMessage());
        $game->setViewTemplate('html/empty.twig');
    }
}
