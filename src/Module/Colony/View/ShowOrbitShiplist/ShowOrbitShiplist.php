<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowOrbitShiplist;

use Stu\Component\Colony\OrbitShipListRetrieverInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;

final class ShowOrbitShiplist implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_ORBIT_SHIPLIST';

    private ColonyLoaderInterface $colonyLoader;

    private ShowOrbitShiplistRequestInterface $showOrbitShiplistRequest;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private OrbitShipListRetrieverInterface $orbitShipListRetriever;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShowOrbitShiplistRequestInterface $showOrbitShiplistRequest,
        OrbitShipListRetrieverInterface $orbitShipListRetriever,
        ShipWrapperFactoryInterface $shipWrapperFactory
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showOrbitShiplistRequest = $showOrbitShiplistRequest;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->orbitShipListRetriever = $orbitShipListRetriever;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            $this->showOrbitShiplistRequest->getColonyId(),
            $userId,
            false
        );

        $orbitShipList = [];

        foreach ($this->orbitShipListRetriever->retrieve($colony) as $array) {
            foreach ($array['ships'] as $ship) {
                $orbitShipList[] = $this->shipWrapperFactory->wrapShip($ship);
            }
        }


        $game->setPageTitle(_('Schiffe im Orbit'));
        $game->setMacroInAjaxWindow('html/colonymacros.xhtml/orbitshiplist');
        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar(
            'WRAPPERS',
            $orbitShipList
        );
    }
}
