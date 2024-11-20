<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowStationInfo;

use Override;
use request;
use Stu\Component\Station\StationEnum;
use Stu\Component\Station\StationUtilityInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\ShipRumpUserRepositoryInterface;

final class ShowStationInfo implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_STATION_INFO';

    public function __construct(private StationUtilityInterface $stationUtility, private ShipRumpUserRepositoryInterface $shipRumpUserRepository) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setPageTitle(_('Baukosten'));

        $userId = $game->getUser()->getId();
        $planId = request::getIntFatal('pid');

        $plan = $this->stationUtility->getBuidplanIfResearchedByUser($planId, $userId);
        $rump = $plan->getRump();

        if (!$this->shipRumpUserRepository->isAvailableForUser($rump->getId(), $userId)) {
            return;
        }

        $game->setMacroInAjaxWindow('html/station/stationInfo.twig');

        $game->setTemplateVar('PLAN', $plan);

        $limit = StationEnum::BUILDABLE_LIMITS_PER_ROLE[$rump->getRoleId()];
        $game->setTemplateVar('LIMIT', $limit === PHP_INT_MAX ? 'unbegrenzt' : $limit);

        $location = StationEnum::BUILDABLE_LOCATIONS_PER_ROLE[$rump->getRoleId()];
        $game->setTemplateVar('LOCATION', $location);
    }
}
