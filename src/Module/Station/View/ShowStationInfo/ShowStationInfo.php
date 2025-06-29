<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowStationInfo;

use Override;
use request;
use RuntimeException;
use Stu\Component\Station\StationEnum;
use Stu\Component\Station\StationUtilityInterface;
use Stu\Exception\SanityCheckException;
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
        $planId = request::getIntFatal('planid');

        $plan = $this->stationUtility->getBuidplanIfResearchedByUser($planId, $userId);
        if ($plan === null) {
            throw new SanityCheckException(sprintf('planId %d is not researched by userId %d', $planId, $userId));
        }

        $rump = $plan->getRump();

        if (!$this->shipRumpUserRepository->isAvailableForUser($rump->getId(), $userId)) {
            return;
        }

        $role = $rump->getRoleId();
        if ($role === null) {
            throw new RuntimeException(sprintf('No rump role for rumpId %d, planId %d', $rump->getId(), $planId));
        }

        $game->setMacroInAjaxWindow('html/station/stationInfo.twig');

        $game->setTemplateVar('PLAN', $plan);

        $limit = StationEnum::BUILDABLE_LIMITS_PER_ROLE[$role->value];
        $game->setTemplateVar('LIMIT', $limit === PHP_INT_MAX ? 'unbegrenzt' : $limit);

        $location = StationEnum::BUILDABLE_LOCATIONS_PER_ROLE[$role->value];
        $game->setTemplateVar('LOCATION', $location);
    }
}
