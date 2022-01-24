<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\Overview;

use Stu\Component\Game\GameEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class Overview implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SHIP_LIST';

    private FleetRepositoryInterface $fleetRepository;

    private ShipRepositoryInterface $shipRepository;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        FleetRepositoryInterface $fleetRepository,
        ShipRepositoryInterface $shipRepository,
        LoggerUtilInterface $loggerUtil
    ) {
        $this->fleetRepository = $fleetRepository;
        $this->shipRepository = $shipRepository;
        $this->loggerUtil = $loggerUtil;
    }

    public function handle(GameControllerInterface $game): void
    {

        $userId = $game->getUser()->getId();

        //$this->loggerUtil->init('stu', LoggerEnum::LEVEL_ERROR);

        $this->loggerUtil->log(sprintf('Shiplist-start, timestamp: %F', microtime(true)));

        $fleets = $this->fleetRepository->getByUser($userId);
        $ships = $this->shipRepository->getByUserAndFleetAndBase($userId, null, false);

        $game->appendNavigationPart(
            'ship.php',
            _('Schiffe')
        );
        $game->setPageTitle(_('/ Schiffe'));
        $game->setTemplateFile('html/shiplist.xhtml');

        $game->setTemplateVar('MAX_CREW_PER_FLEET', GameEnum::CREW_PER_FLEET);
        $game->setTemplateVar('SHIPS_AVAILABLE', $fleets !== [] || $ships !== []);
        $game->setTemplateVar('FLEETS', $fleets);
        $game->setTemplateVar('SHIPS', $ships);

        $this->loggerUtil->log(sprintf('Shiplist-end, timestamp: %F', microtime(true)));
    }
}
