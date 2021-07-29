<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowStationCosts;

use request;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;

final class ShowStationCosts implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_STATION_COSTS';

    private ShipBuildplanRepositoryInterface $shipBuildplanRepository;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ShipBuildplanRepositoryInterface $shipBuildplanRepository,
        LoggerUtilInterface $loggerUtil
    ) {
        $this->shipBuildplanRepository = $shipBuildplanRepository;
        $this->loggerUtil = $loggerUtil;
    }

    public function handle(GameControllerInterface $game): void
    {
        $this->loggerUtil->init('stu', LoggerEnum::LEVEL_ERROR);

        $this->loggerUtil->log('A');

        $game->setTemplateVar('ERROR', true);

        $game->setPageTitle(_('Baukosten'));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/stationmacros.xhtml/stationcost');

        $userId = $game->getUser()->getId();

        $wantedPlanId = request::getIntFatal('pid');
        $plans = $this->shipBuildplanRepository->getStationBuildplansByUser($userId);

        $this->loggerUtil->log('B');

        $plan = null;
        foreach ($plans as $p) {

            $this->loggerUtil->log('C');
            if ($p->getId() === $wantedPlanId) {
                $this->loggerUtil->log('D');
                $plan = $p;
                break;
            }
        }

        $this->loggerUtil->log('E');


        if ($plan === null) {
            return;
        }

        $this->loggerUtil->log('F');

        $game->setTemplateVar('PLAN', $plan);
        $game->setTemplateVar('ERROR', false);
    }
}
