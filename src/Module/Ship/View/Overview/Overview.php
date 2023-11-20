<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\Overview;

use Stu\Component\Game\GameEnum;
use Stu\Component\Ship\SpacecraftTypeEnum;
use Stu\Lib\SessionInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class Overview implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SHIP_LIST';

    private FleetRepositoryInterface $fleetRepository;

    private ShipRepositoryInterface $shipRepository;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private SessionInterface $session;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        FleetRepositoryInterface $fleetRepository,
        ShipRepositoryInterface $shipRepository,
        ShipWrapperFactoryInterface $shipWrapperFactory,
        SessionInterface $session,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->fleetRepository = $fleetRepository;
        $this->shipRepository = $shipRepository;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->session = $session;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        //$this->loggerUtil->init('stu', LoggerEnum::LEVEL_ERROR);

        $this->loggerUtil->log(sprintf('Shiplist-start, timestamp: %F', microtime(true)));

        $fleets = $this->fleetRepository->getByUser($userId);
        $ships = $this->shipRepository->getByUserAndFleetAndType($userId, null, SpacecraftTypeEnum::SPACECRAFT_TYPE_SHIP);

        foreach ($fleets as $fleet) {
            $fleet->setHiddenStyle($this->session->hasSessionValue('hiddenshiplistfleets', $fleet->getId()) ? 'display: none' : '');
        }

        $game->appendNavigationPart(
            'ship.php',
            _('Schiffe')
        );
        $game->setPageTitle(_('/ Schiffe'));
        $game->setTemplateFile('html/shiplist.twig');

        $game->setTemplateVar('MAX_CREW_PER_FLEET', GameEnum::CREW_PER_FLEET);
        $game->setTemplateVar('SHIPS_AVAILABLE', $fleets !== [] || $ships !== []);
        $game->setTemplateVar('FLEETWRAPPERS', $this->shipWrapperFactory->wrapFleets($fleets));
        $game->setTemplateVar('SINGLESHIPWRAPPERS', $this->shipWrapperFactory->wrapShips($ships));

        $this->loggerUtil->log(sprintf('Shiplist-end, timestamp: %F', microtime(true)));
    }
}
