<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowOrbitShiplist;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Tal\OrbitShipItem;
use Stu\Module\Tal\OrbitShipItemInterface;
use Stu\Orm\Entity\ShipInterface;

final class ShowOrbitShiplist implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_ORBIT_SHIPLIST';

    private ColonyLoaderInterface $colonyLoader;

    private ShowOrbitShiplistRequestInterface $showOrbitShiplistRequest;

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

        $orbitShipList = [];

        foreach ($colony->getOrbitShipList($userId) as $entry) {
            $entry['ships'] = array_map(
                function (ShipInterface $ship) use ($game): OrbitShipItemInterface {
                    return new OrbitShipItem($ship, $game);
                },
                $entry['ships']
            );
            $orbitShipList[] = $entry;
        }

        $game->setPageTitle(_('Schiffe im Orbit'));
        $game->setMacroInAjaxWindow('html/colonymacros.xhtml/orbitshiplist');
        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar(
            'ORBIT_SHIP_LIST',
            $orbitShipList
        );
    }
}
