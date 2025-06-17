<?php

namespace Stu\Module\Control\Router\Handler;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\Router\FallbackRouteException;

class LoginErrorFallbackHandler implements FallbackHandlerInterface
{
    public function handle(FallbackRouteException $e, GameControllerInterface $game): void
    {
        $game->setTemplateVar('LOGIN_ERROR', $e->getMessage());
        $game->setTemplateFile('html/index/index.twig');
    }
}
