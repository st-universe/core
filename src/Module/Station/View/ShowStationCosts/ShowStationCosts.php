<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowStationCosts;

use Override;
use request;
use Stu\Component\Station\StationUtilityInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class ShowStationCosts implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_STATION_COSTS';

    public function __construct(private ShipLoaderInterface $shipLoader, private StationUtilityInterface $stationUtility) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $ship = $this->shipLoader->getByIdAndUser(
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
        foreach ($plan->getModules() as $mod) {
            $mods[] = new StationCostWrapper($mod, $ship->getStorage()->get($mod->getModule()->getCommodityId()));
        }
        $game->setTemplateVar('MODS', $mods);

        $dockedWorkbees = $this->stationUtility->getDockedWorkbeeCount($ship);
        $game->setTemplateVar('DOCKED', $dockedWorkbees);
        $game->setTemplateVar('WORKBEECOLOR', $dockedWorkbees < $plan->getRump()->getNeededWorkbees() ? 'red' : 'green');
    }
}
