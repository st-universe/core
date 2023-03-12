<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowStationInfo;

use request;
use Stu\Component\Station\StationEnum;
use Stu\Component\Station\StationUtilityInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Repository\ShipRumpUserRepositoryInterface;

final class ShowStationInfo implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_STATION_INFO';

    private StationUtilityInterface $stationUtility;

    private ShipRumpUserRepositoryInterface $shipRumpUserRepository;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        StationUtilityInterface $stationUtility,
        ShipRumpUserRepositoryInterface $shipRumpUserRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->stationUtility = $stationUtility;
        $this->shipRumpUserRepository = $shipRumpUserRepository;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateVar('ERROR', true);

        $game->setPageTitle(_('Baukosten'));
        $game->setMacroInAjaxWindow('html/stationmacros.xhtml/stationinfo');

        $userId = $game->getUser()->getId();
        $planId = (int)request::getIntFatal('pid');

        $plan = $this->stationUtility->getBuidplanIfResearchedByUser($planId, $userId);
        $rump = $plan->getRump();

        if (!$this->shipRumpUserRepository->isAvailableForUser($rump->getId(), $userId)) {
            return;
        }

        $game->setTemplateVar('PLAN', $plan);

        $limit = StationEnum::BUILDABLE_LIMITS_PER_ROLE[$rump->getRoleId()];
        $game->setTemplateVar('LIMIT', $limit === PHP_INT_MAX ? 'unbegrenzt' : $limit);

        $location = StationEnum::BUILDABLE_LOCATIONS_PER_ROLE[$rump->getRoleId()];
        $game->setTemplateVar('LOCATION', $location);

        $game->setTemplateVar('ERROR', false);
    }
}
