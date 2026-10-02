<?php

declare(strict_types=1);

namespace Stu\Module\Control\Router\Handler;

use Stu\Lib\UserLockedException;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\Router\FallbackRouteException;

class UserLockedFallbackHandler implements FallbackHandlerInterface
{
    #[\Override]
    public function handle(FallbackRouteException $e, ViewControllerContext $context): void
    {
        $context->setTemplateFile('html/index/accountLocked.twig');
        $context->setTemplateVar('LOGIN_ERROR', $e->getMessage());

        if ($e instanceof UserLockedException) {
            $context->setTemplateVar('REASON', $e->getDetails());
        }
    }
}
