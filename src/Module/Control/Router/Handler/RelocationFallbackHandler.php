<?php

namespace Stu\Module\Control\Router\Handler;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\Router\FallbackRouteException;

class RelocationFallbackHandler implements FallbackHandlerInterface
{
    #[\Override]
    public function handle(FallbackRouteException $e, GameControllerInterface $game): void
    {
        $game->setPageTitle('Umzugsmodus');
        $game->setTemplateFile('html/index/relocation.twig');
    }
}
