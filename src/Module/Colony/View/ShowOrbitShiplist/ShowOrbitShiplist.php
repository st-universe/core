<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowOrbitShiplist;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
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

        $game->setPageTitle(_('Schiffe im Orbit'));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/colonymacros.xhtml/orbitshiplist');
        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('ORBIT_SHIP_LIST', $colony->getOrbitShipList($userId));
    }
}
