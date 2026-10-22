<?php

declare(strict_types=1);

namespace Stu\Lib\Request;

use Stu\Module\Control\Router\FallbackRouteException;

final class RequestLimitException extends FallbackRouteException
{
    public function __construct(private int $retryAfter)
    {
        parent::__construct(_('Zu viele Anfragen. Bitte warte kurz und versuche es erneut'));
    }

    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }
}
