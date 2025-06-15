<?php

namespace Stu\Module\Control\Router\Handler;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\Router\FallbackRouteException;

class AccountNotVerifiedFallbackHandler implements FallbackHandlerInterface
{
    public function handle(FallbackRouteException $e, GameControllerInterface $game): void
    {
        $game->setTemplateFile('html/index/accountVerification.twig');
        if ($e->getMessage() !== '') {
            $game->setTemplateVar('REASON', $e->getMessage());
        }
        $user = $game->getUser();
        $game->setTemplateVar('HAS_MOBILE', $user->getMobile() !== null);
        $game->setTemplateVar('USER', $user);
        $game->setTemplateVar('MAIL', $user->getEmail());
        $game->setTemplateVar('MOBILE', $user->getMobile());
        $game->setTemplateVar('SMS_ATTEMPTS_LEFT', 3 - $user->getSmsSended());
    }
}
