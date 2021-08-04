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

        $bases = $this->shipRepository->getByUserAndFleetAndBase($userId, null, true);

        $game->appendNavigationPart(
            'station.php',
            _('Stationen')
        );
        $game->setPageTitle(_('/ Stationen'));
        $game->setTemplateFile('html/stationlist.xhtml');

        $game->setTemplateVar('BASES', $bases);
    }
}
