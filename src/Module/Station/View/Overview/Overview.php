<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\Overview;

use Stu\Component\Game\GameEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class Overview implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_STATION_LIST';


    private ShipRepositoryInterface $shipRepository;

    public function __construct(
        ShipRepositoryInterface $shipRepository
    ) {
        $this->shipRepository = $shipRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $fleets = [];
        $bases = $this->shipRepository->getByUserAndFleetAndBase($userId, null, true);
        $ships = [];

        $game->appendNavigationPart(
            'station.php',
            _('Stationen')
        );
        $game->setPageTitle(_('/ Stationen'));
        //TODO shiplist separation != stations
        $game->setTemplateFile('html/shiplist.xhtml');

        //TODO clean vars
        $game->setTemplateVar('MAX_CREW_PER_FLEET', GameEnum::CREW_PER_FLEET);
        $game->setTemplateVar(
            'SHIPS_AVAILABLE',
            $fleets !== [] || $ships !== [] || $bases !== []
        );
        $game->setTemplateVar(
            'FLEETS',
            $fleets
        );
        $game->setTemplateVar(
            'BASES',
            $bases
        );
        $game->setTemplateVar(
            'SHIPS',
            $ships
        );
    }
}
