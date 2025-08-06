<?php

declare(strict_types=1);

namespace Stu\Component\Game;

use Stu\Module\Control\Router\FallbackRouteException;

class RedirectionException extends FallbackRouteException
{
    public function __construct(private string $href)
    {
        parent::__construct($href);
    }

    public function getHref(): string
    {
        return $this->href;
    }
}
