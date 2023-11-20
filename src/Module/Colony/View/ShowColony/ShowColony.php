<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowColony;

use request;
use Stu\Component\Building\BuildingEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Component\Colony\OrbitShipListRetrieverInterface;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Database\View\Category\Tal\DatabaseCategoryTalFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;

final class ShowColony implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_COLONY';

    private ColonyLoaderInterface $colonyLoader;

    private ColonyGuiHelperInterface $colonyGuiHelper;

    private ShowColonyRequestInterface $showColonyRequest;

    private DatabaseCategoryTalFactoryInterface $databaseCategoryTalFactory;

    private TorpedoTypeRepositoryInterface $torpedoTypeRepository;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private OrbitShipListRetrieverInterface $orbitShipListRetriever;

    private ColonyFunctionManagerInterface $colonyFunctionManager;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyGuiHelperInterface $colonyGuiHelper,
        ShowColonyRequestInterface $showColonyRequest,
        TorpedoTypeRepositoryInterface $torpedoTypeRepository,
        DatabaseCategoryTalFactoryInterface $databaseCategoryTalFactory,
        OrbitShipListRetrieverInterface $orbitShipListRetriever,
        ColonyFunctionManagerInterface $colonyFunctionManager,
        ShipWrapperFactoryInterface $shipWrapperFactory
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyGuiHelper = $colonyGuiHelper;
        $this->showColonyRequest = $showColonyRequest;
        $this->databaseCategoryTalFactory = $databaseCategoryTalFactory;
        $this->torpedoTypeRepository = $torpedoTypeRepository;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->orbitShipListRetriever = $orbitShipListRetriever;
        $this->colonyFunctionManager = $colonyFunctionManager;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showColonyRequest->getColonyId(),
            $userId,
            false
        );

        $menu = ColonyMenuEnum::getFor($game->getViewContext()['COLONY_MENU'] ?? null);
        $this->colonyGuiHelper->registerComponents($colony, $game);
        $game->setTemplateVar('CURRENT_MENU', $menu);

        $firstOrbitShip = null;

        $shipList = $this->orbitShipListRetriever->retrieve($colony);
        if ($shipList !== []) {
            // if selected, return the current target
            $target = request::indInt('target');

            if ($target !== 0) {
                foreach ($shipList as $fleet) {
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

        $game->appendNavigationPart(
            'colony.php',
            _('Kolonien')
        );
        $game->appendNavigationPart(
            sprintf('?%s=1&id=%d', static::VIEW_IDENTIFIER, $colony->getId()),
            $colony->getName()
        );
        $game->setTemplateFile('html/colony/colony.twig');
        $game->setPagetitle(sprintf(_('Kolonie: %s'), $colony->getName()));


        $game->setTemplateVar('SELECTED_COLONY_MENU_TEMPLATE', $menu->getTemplate());

        $starsystem = $this->databaseCategoryTalFactory->createDatabaseCategoryEntryTal($colony->getSystem()->getDatabaseEntry(), $user);
        $game->setTemplateVar('STARSYSTEM_ENTRY_TAL', $starsystem);

        $game->setTemplateVar('FIRST_ORBIT_SHIP', $firstOrbitShip ? $this->shipWrapperFactory->wrapShip($firstOrbitShip) : null);

        $particlePhalanx = $this->colonyFunctionManager->hasFunction($colony, BuildingEnum::BUILDING_FUNCTION_PARTICLE_PHALANX);
        $game->setTemplateVar(
            'BUILDABLE_TORPEDO_TYPES',
            $particlePhalanx ? $this->torpedoTypeRepository->getForUser($userId) : null
        );
    }
}
