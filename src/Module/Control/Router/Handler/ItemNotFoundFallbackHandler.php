<?php

namespace Stu\Module\Control\Router\Handler;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\Router\FallbackRouteException;

class ItemNotFoundFallbackHandler implements FallbackHandlerInterface
{
    #[\Override]
    public function handle(FallbackRouteException $e, GameControllerInterface $game): void
    {
        $game->getInfo()->addInformation('Das angeforderte Item wurde nicht gefunden');
        $game->setViewTemplate('html/empty.twig');
    }
}
