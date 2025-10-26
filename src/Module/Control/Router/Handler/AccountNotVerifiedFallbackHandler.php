<?php

namespace Stu\Module\Control\Router\Handler;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\Router\FallbackRouteException;

class AccountNotVerifiedFallbackHandler implements FallbackHandlerInterface
{
    #[\Override]
    public function handle(FallbackRouteException $e, GameControllerInterface $game): void
    {
        $game->setTemplateFile('html/index/accountVerification.twig');
        if ($e->getMessage() !== '') {
            $game->setTemplateVar('REASON', $e->getMessage());
        }
        $user = $game->getUser();
        $registration = $user->getRegistration();

        $game->setTemplateVar('HAS_MOBILE', $registration->getMobile() !== null);
        $game->setTemplateVar('USER', $user);
        $game->setTemplateVar('MAIL', $registration->getEmail());
        $game->setTemplateVar('MOBILE', $registration->getMobile());
        $game->setTemplateVar('SMS_ATTEMPTS_LEFT', 3 - $registration->getSmsSended());
    }
}
