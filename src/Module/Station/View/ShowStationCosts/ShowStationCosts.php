<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowStationCosts;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;

final class ShowStationCosts implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_STATION_COSTS';

    private ShipLoaderInterface $shipLoader;

    private ShipBuildplanRepositoryInterface $shipBuildplanRepository;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipBuildplanRepositoryInterface $shipBuildplanRepository,
        LoggerUtilInterface $loggerUtil
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipBuildplanRepository = $shipBuildplanRepository;
        $this->loggerUtil = $loggerUtil;
    }

    public function handle(GameControllerInterface $game): void
    {
        $this->loggerUtil->init('stu', LoggerEnum::LEVEL_ERROR);

        $this->loggerUtil->log('A');

        $game->setTemplateVar('ERROR', true);

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $game->setPageTitle(_('Baukosten'));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/stationmacros.xhtml/stationcost');

        $userId = $game->getUser()->getId();

        $wantedPlanId = (int)request::getIntFatal('pid');
        $plans = $this->shipBuildplanRepository->getStationBuildplansByUser($userId);

        $this->loggerUtil->log(sprintf('wantedPlanId: %d', $wantedPlanId));

        $plan = null;
        foreach ($plans as $p) {

            $this->loggerUtil->log(sprintf('p->id: %d', $p->getId()));
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

        $mods = [];
        foreach ($plan->getModules() as $mod) {
            $mods[] = new StationCostWrapper($mod, $ship->getStorage()->get($mod->getModule()->getGoodId()));
        }
        $game->setTemplateVar('MODS', $mods);

        $dockedWorkbees = 0;
        foreach ($ship->getDockedShips() as $docked) {
            $commodity = $docked->getRump()->getCommodity();
            if ($commodity !== null && $commodity->isWorkbee()) {
                $dockedWorkbees += 1;
            }
        }

        $game->setTemplateVar('DOCKED', $dockedWorkbees);
        $game->setTemplateVar('WORKBEECOLOR', $dockedWorkbees < $plan->getRump()->getNeededWorkbees() ? 'red' : 'green');

        $game->setTemplateVar('ERROR', false);
    }
}
