<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowOrbitShiplist;

use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;

final class ShowOrbitShiplist implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_ORBIT_SHIPLIST';

    private $colonyLoader;

    private $showOrbitShiplistRequest;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShowOrbitShiplistRequestInterface $showOrbitShiplistRequest
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showOrbitShiplistRequest = $showOrbitShiplistRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showOrbitShiplistRequest->getColonyId(),
            $userId
        );

        $game->setPageTitle(sprintf(_('%d Schiffe im Orbit'), $colony->getShipListCount()));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/colonymacros.xhtml/orbitshiplist');
        $game->setTemplateVar('COLONY', $colony);
    }
}
