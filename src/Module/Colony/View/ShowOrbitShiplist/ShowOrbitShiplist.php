<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowOrbitShiplist;

use Stu\Component\Colony\OrbitShipWrappersRetrieverInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowOrbitShiplist implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_ORBIT_SHIPLIST';

    public function __construct(
        private ColonyLoaderInterface $colonyLoader,
        private ShowOrbitShiplistRequestInterface $showOrbitShiplistRequest,
        private OrbitShipWrappersRetrieverInterface $orbitShipWrappersRetriever
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            $this->showOrbitShiplistRequest->getColonyId(),
            $userId,
            false
        );

        $game->setPageTitle('Schiffe im Orbit');
        $game->setMacroInAjaxWindow('html/colony/orbitShipList.twig');
        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('GROUPS', $this->orbitShipWrappersRetriever->retrieve($colony));
    }
}
