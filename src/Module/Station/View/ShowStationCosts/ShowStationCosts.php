<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowStationCosts;

use request;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;

final class ShowStationCosts implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_STATION_COSTS';

    private ShipBuildplanRepositoryInterface $shipBuildplanRepository;

    public function __construct(
        ShipBuildplanRepositoryInterface $shipBuildplanRepository
    ) {
        $this->shipBuildplanRepository = $shipBuildplanRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateVar('ERROR', true);

        $game->setPageTitle(_('Baukosten'));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/stationmacros.xhtml/stationcost');

        $userId = $game->getUser()->getId();

        $wantedPlanId = request::getIntFatal('pid');
        $plans = $this->shipBuildplanRepository->getStationBuildplansByUser($userId);

        $plan = null;
        foreach ($plans as $p) {
            if ($p->getId() === $wantedPlanId) {
                $plan = $p;
                break;
            }
        }

        if ($plan === null) {
            return;
        }

        $game->setTemplateVar('PLAN', $plan);
        $game->setTemplateVar('ERROR', false);
    }
}
