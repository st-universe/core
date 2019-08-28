<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowSurface;

use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;

final class ShowSurface implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_COLONY_SURFACE';

    private $colonyLoader;

    private $showSurfaceRequest;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShowSurfaceRequestInterface $showSurfaceRequest
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showSurfaceRequest = $showSurfaceRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showSurfaceRequest->getColonyId(),
            $userId
        );

        $game->setTemplateVar('COLONY', $colony);
        $game->showMacro('html/colonymacros.xhtml/colonysurface');
    }
}
