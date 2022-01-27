<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowStationCosts;

use request;
use Stu\Component\Station\StationUtilityInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class ShowStationCosts implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_STATION_COSTS';

    private ShipLoaderInterface $shipLoader;

    private LoggerUtilInterface $loggerUtil;

    private StationUtilityInterface $stationUtility;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        StationUtilityInterface $stationUtility,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
        $this->stationUtility = $stationUtility;
    }

    public function handle(GameControllerInterface $game): void
    {
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

        $plan = $this->stationUtility->getBuidplanIfResearchedByUser($wantedPlanId, $userId);

        if ($plan === null) {
            return;
        }

        $game->setTemplateVar('PLAN', $plan);

        $mods = [];
        foreach ($plan->getModules() as $mod) {
            $mods[] = new StationCostWrapper($mod, $ship->getStorage()->get($mod->getModule()->getGoodId()));
        }
        $game->setTemplateVar('MODS', $mods);

        $dockedWorkbees = $this->stationUtility->getDockedWorkbeeCount($ship);
        $game->setTemplateVar('DOCKED', $dockedWorkbees);
        $game->setTemplateVar('WORKBEECOLOR', $dockedWorkbees < $plan->getRump()->getNeededWorkbees() ? 'red' : 'green');

        $game->setTemplateVar('ERROR', false);
    }
}
