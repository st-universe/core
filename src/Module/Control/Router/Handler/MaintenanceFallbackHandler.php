<?php

namespace Stu\Module\Control\Router\Handler;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\Router\FallbackRouteException;

class MaintenanceFallbackHandler implements FallbackHandlerInterface
{
    #[\Override]
    public function handle(FallbackRouteException $e, GameControllerInterface $game): void
    {
        $game->setPageTitle('Wartungsmodus');
        $game->setTemplateFile('html/index/maintenance.twig');
    }
}
