<?php

declare(strict_types=1);

namespace Stu\Module\Control\Router\Handler;

use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\Router\FallbackRouteException;

class AccountNotVerifiedFallbackHandler implements FallbackHandlerInterface
{
    #[\Override]
    public function handle(FallbackRouteException $e, ViewControllerContext $context): void
    {
        $context->setTemplateFile('html/index/accountVerification.twig');
        if ($e->getMessage() !== '') {
            $context->setTemplateVar('REASON', $e->getMessage());
        }
        $user = $context->getUser();
        $registration = $user->getRegistration();

        $context->setTemplateVar('HAS_MOBILE', $registration->getMobile() !== null);
        $context->setTemplateVar('USER', $user);
        $context->setTemplateVar('MAIL', $registration->getEmail());
        $context->setTemplateVar('MOBILE', $registration->getMobile());
        $context->setTemplateVar('SMS_ATTEMPTS_LEFT', 3 - $registration->getSmsSended());
    }
}
