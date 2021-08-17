<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowColony;

use ColonyMenu;
use request;
use Stu\Component\Building\BuildingEnum;
use Stu\Component\Colony\ColonyEnum;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Tal\OrbitShipItem;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;

final class ShowColony implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_COLONY';

    private ColonyLoaderInterface $colonyLoader;

    private ColonyGuiHelperInterface $colonyGuiHelper;

    private ShowColonyRequestInterface $showColonyRequest;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private TorpedoTypeRepositoryInterface $torpedoTypeRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyGuiHelperInterface $colonyGuiHelper,
        ShowColonyRequestInterface $showColonyRequest,
        ColonyLibFactoryInterface $colonyLibFactory,
        TorpedoTypeRepositoryInterface $torpedoTypeRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyGuiHelper = $colonyGuiHelper;
        $this->showColonyRequest = $showColonyRequest;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->torpedoTypeRepository = $torpedoTypeRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showColonyRequest->getColonyId(),
            $userId
        );

        $this->colonyGuiHelper->register($colony, $game);

        $menuId = $game->getViewContext()['COLONY_MENU'] ?? ColonyEnum::MENU_INFO;

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

        $game->appendNavigationPart(
            'colony.php',
            _('Kolonien')
        );
        $game->appendNavigationPart(
            sprintf('?%s=1&id=%d', static::VIEW_IDENTIFIER, $colony->getId()),
            $colony->getName()
        );
        $game->setTemplateFile('html/colony.xhtml');
        $game->setPagetitle(sprintf(_('Kolonie: %s'), $colony->getName()));

        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar(
            'SELECTED_COLONY_MENU',
            $this->colonyGuiHelper->getColonyMenu($menuId)
        );
        $game->setTemplateVar(
            'COLONY_MENU_SELECTOR',
            new ColonyMenu($menuId)
        );
        $game->setTemplateVar('FIRST_ORBIT_SHIP', $firstOrbitShip ? new OrbitShipItem($firstOrbitShip) : null);
        $game->setTemplateVar('COLONY_SURFACE', $this->colonyLibFactory->createColonySurface($colony));
        $game->setTemplateVar('IMMIGRATION_SYMBOL', $immigrationSymbol);

        $particlePhalanxCount = $colony->getBuildingWithFunctionCount(BuildingEnum::BUILDING_FUNCTION_PARTICLE_PHALANX, [0, 1]);
        $game->setTemplateVar('BUILDABLE_TORPEDO_TYPES', $particlePhalanxCount > 0 ? $this->torpedoTypeRepository->getForUser($userId) : null);
    }
}
