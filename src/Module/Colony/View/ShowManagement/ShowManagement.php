<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowManagement;

use ColonyMenu;
use request;
use Stu\Component\Colony\ColonyEnum;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;

final class ShowManagement implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_MANAGEMENT';

    private $colonyLoader;

    private $colonyGuiHelper;

    private $showManagementRequest;

    private $colonyLibFactory;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyGuiHelperInterface $colonyGuiHelper,
        ShowManagementRequestInterface $showManagementRequest,
        ColonyLibFactoryInterface $colonyLibFactory
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyGuiHelper = $colonyGuiHelper;
        $this->showManagementRequest = $showManagementRequest;
        $this->colonyLibFactory = $colonyLibFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showManagementRequest->getColonyId(),
            $userId
        );

        $this->colonyGuiHelper->register($colony, $game);

        $firstOrbitShip = null;

        $shipList = $colony->getOrbitShipList($userId);
        if ($shipList !== []) {
            // if selected, return the current target
            $target = request::postInt('target');

            if ($target) {
                foreach ($shipList as $key => $fleet) {
                    foreach ($fleet['ships'] as $idx => $ship) {
                        if ($idx == $target) {
                            $firstOrbitShip = $ship;
                        }
                    }
                }
            }
            if ($firstOrbitShip === null) {
                $firstOrbitShip = current(current($shipList)['ships']);
            }
        }

        $immigrationSymbol = '-';
        if ($colony->getImmigration() > 0) {
            $immigrationSymbol = '+';
        }
        if ($colony->getImmigration() == 0) {
            $immigrationSymbol = '';
        }

        $game->showMacro('html/colonymacros.xhtml/cm_management');

        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('COLONY_MENU_SELECTOR', new ColonyMenu(ColonyEnum::MENU_INFO));
        $game->setTemplateVar('FIRST_ORBIT_SHIP', $firstOrbitShip);
        $game->setTemplateVar('COLONY_SURFACE', $this->colonyLibFactory->createColonySurface($colony));
        $game->setTemplateVar('IMMIGRATION_SYMBOL', $immigrationSymbol);
    }
}
