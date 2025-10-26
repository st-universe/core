<?php

namespace Stu\Module\Control\Router\Handler;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\Router\FallbackRouteException;

class ResetFallbackHandler implements FallbackHandlerInterface
{
    #[\Override]
    public function handle(FallbackRouteException $e, GameControllerInterface $game): void
    {
        $game->setPageTitle('Resetmodus');
        $game->setTemplateFile('html/index/gameReset.twig');
    }
}
