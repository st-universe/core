<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowStationCosts;

use request;
use Stu\Component\Station\StationUtilityInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Station\Lib\StationLoaderInterface;

final class ShowStationCosts implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_STATION_COSTS';

    public function __construct(private StationLoaderInterface $stationLoader, private StationUtilityInterface $stationUtility) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $station = $this->stationLoader->getByIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId(),
            false,
            false
        );

        $game->setPageTitle(_('Baukosten'));
        $game->setMacroInAjaxWindow('html/station/stationCost.twig');

        $userId = $game->getUser()->getId();

        $wantedPlanId = request::getIntFatal('planid');

        $plan = $this->stationUtility->getBuidplanIfResearchedByUser($wantedPlanId, $userId);

        if ($plan === null) {
            return;
        }

        $game->setTemplateVar('PLAN', $plan);

        $mods = [];
        foreach ($plan->getModulesOrdered() as $mod) {
            $mods[] = new StationCostWrapper($mod, $station->getStorage()->get($mod->getModule()->getCommodityId()));
        }
        $game->setTemplateVar('MODS', $mods);

        $dockedWorkbees = $station->getDockedWorkbeeCount();
        $game->setTemplateVar('DOCKED', $dockedWorkbees);
        $game->setTemplateVar('WORKBEECOLOR', $dockedWorkbees < $plan->getRump()->getNeededWorkbees() ? 'red' : 'green');
    }
}
