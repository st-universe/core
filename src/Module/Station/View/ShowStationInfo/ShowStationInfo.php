<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowStationInfo;

use request;
use Stu\Component\Station\StationEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;
use Stu\Orm\Repository\ShipRumpUserRepositoryInterface;

final class ShowStationInfo implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_STATION_INFO';

    private ShipRumpUserRepositoryInterface $shipRumpUserRepository;

    private ShipRumpRepositoryInterface $shipRumpRepository;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ShipRumpUserRepositoryInterface $shipRumpUserRepository,
        ShipRumpRepositoryInterface $shipRumpRepository,
        LoggerUtilInterface $loggerUtil
    ) {
        $this->shipRumpUserRepository = $shipRumpUserRepository;
        $this->shipRumpRepository = $shipRumpRepository;
        $this->loggerUtil = $loggerUtil;
    }

    public function handle(GameControllerInterface $game): void
    {
        $this->loggerUtil->init();

        $game->setTemplateVar('ERROR', true);

        $game->setPageTitle(_('Baukosten'));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/stationmacros.xhtml/stationinfo');

        $userId = $game->getUser()->getId();

        $rumpId = (int)request::getIntFatal('rid');

        if (!$this->shipRumpUserRepository->isAvailableForUser($rumpId, $userId)) {
            return;
        }

        $rump = $this->shipRumpRepository->find($rumpId);
        $game->setTemplateVar('RUMP', $rump);

        $limit = StationEnum::BUILDABLE_LIMITS_PER_ROLE[$rump->getRoleId()];
        $game->setTemplateVar('LIMIT', $limit === PHP_INT_MAX ? 'unbegrenzt' : $limit);

        $location = StationEnum::BUILDABLE_LOCATIONS_PER_ROLE[$rump->getRoleId()];
        $game->setTemplateVar('LOCATION', $location);

        $game->setTemplateVar('ERROR', false);
    }
}
