<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowOrbitShiplist;

use request;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;

final class ShowOrbitShiplist implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_ORBIT_SHIPLIST';

    private $colonyLoader;

    private $colonyGuiHelper;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyGuiHelperInterface $colonyGuiHelper
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyGuiHelper = $colonyGuiHelper;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $userId
        );

        $game->setPageTitle(sprintf(_('%d Schiffe im Orbit'), $colony->getShipListCount()));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setAjaxMacro('html/colonymacros.xhtml/orbitshiplist');
        $game->setTemplateVar('COLONY', $colony);
    }
}
