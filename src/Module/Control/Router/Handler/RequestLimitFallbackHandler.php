<?php

declare(strict_types=1);

namespace Stu\Module\Control\Router\Handler;

use LogicException;
use Stu\Lib\Request\RequestLimitException;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\Router\FallbackRouteException;

final class RequestLimitFallbackHandler implements FallbackHandlerInterface
{
    #[\Override]
    public function handle(FallbackRouteException $e, ViewControllerContext $context): void
    {
        if (!$e instanceof RequestLimitException) {
            throw new LogicException('Falscher Fehler für Anfragelimit');
        }
        http_response_code(429);
        header('Retry-After: ' . $e->getRetryAfter());
        $context->setTemplateVar('LOGIN_ERROR', $e->getMessage());
        $context->setTemplateFile('html/index/index.twig');
    }
}
