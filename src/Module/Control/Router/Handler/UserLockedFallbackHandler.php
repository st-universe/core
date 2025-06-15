<?php

namespace Stu\Module\Control\Router\Handler;

use Stu\Lib\UserLockedException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\Router\FallbackRouteException;

class UserLockedFallbackHandler implements FallbackHandlerInterface
{
    public function handle(FallbackRouteException $e, GameControllerInterface $game): void
    {
        $game->setTemplateFile('html/index/accountLocked.twig');
        $game->setTemplateVar('LOGIN_ERROR', $e->getMessage());

        if ($e instanceof UserLockedException) {
            $game->setTemplateVar('REASON', $e->getDetails());
        }
    }
}
