<?php

namespace Stu\Module\Control\Router;

use Stu\Module\Control\GameControllerInterface;

interface FallbackRouterInterface
{
    public function showFallbackSite(FallbackRouteException $e, GameControllerInterface $game): void;
}
