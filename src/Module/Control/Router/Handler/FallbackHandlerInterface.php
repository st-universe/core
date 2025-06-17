<?php

namespace Stu\Module\Control\Router\Handler;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\Router\FallbackRouteException;

interface FallbackHandlerInterface
{
    public function handle(FallbackRouteException $e, GameControllerInterface $game): void;
}
