<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use Override;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Template\StatusBarColorEnum;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class EpsBarProvider implements PlanetFieldHostComponentInterface
{
    public function __construct(private PlanetFieldRepositoryInterface $planetFieldRepository) {}

    #[Override]
    public function setTemplateVariables(
        $entity,
        GameControllerInterface $game
    ): void {
        $energyProduction = $this->planetFieldRepository->getEnergyProductionByHost($entity);

        $game->setTemplateVar('EPS_STATUS_BAR', $this->getEpsStatusBar($entity, $energyProduction));
        $game->setTemplateVar('EPS_PRODUCTION', $energyProduction);
        $game->setTemplateVar('EPS_BAR_TITLE_STRING', $this->getEpsBarTitleString($entity, $energyProduction));
    }

    public static function getEpsStatusBar(PlanetFieldHostInterface $host, int $energyProduction, int $width = 360): string
    {
        $currentEps = $host instanceof Colony ? $host->getChangeable()->getEps() : 0;

        $bars = [];
        $epsBar = '';
        if ($energyProduction < 0) {
            $prod = abs($energyProduction);
            if ($currentEps - $prod < 0) {
                $bars[StatusBarColorEnum::STATUSBAR_RED] = $currentEps;
                $bars[StatusBarColorEnum::STATUSBAR_GREY] = $host->getMaxEps() - $currentEps;
            } else {
                $bars[StatusBarColorEnum::STATUSBAR_YELLOW] = $currentEps - $prod;
                $bars[StatusBarColorEnum::STATUSBAR_RED] = $prod;
                $bars[StatusBarColorEnum::STATUSBAR_GREY] = $host->getMaxEps() - $currentEps;
            }
        }
        if ($energyProduction > 0) {
            if ($currentEps + $energyProduction > $host->getMaxEps()) {
                $bars[StatusBarColorEnum::STATUSBAR_YELLOW] = $currentEps;
                if ($currentEps < $host->getMaxEps()) {
                    $bars[StatusBarColorEnum::STATUSBAR_GREEN] = $host->getMaxEps() - $currentEps;
                }
            } else {
                $bars[StatusBarColorEnum::STATUSBAR_YELLOW] = $currentEps;
                $bars[StatusBarColorEnum::STATUSBAR_GREEN] = $energyProduction;
                $bars[StatusBarColorEnum::STATUSBAR_GREY] = $host->getMaxEps() - $currentEps - $energyProduction;
            }
        }
        if ($energyProduction == 0) {
            $bars[StatusBarColorEnum::STATUSBAR_YELLOW] = $currentEps;
            $bars[StatusBarColorEnum::STATUSBAR_GREY] = $host->getMaxEps() - $currentEps;
        }
        foreach ($bars as $color => $value) {
            if ($host->getMaxEps() < $value) {
                $value = $host->getMaxEps();
            }
            if ($value <= 0) {
                continue;
            }
            $epsBar .= sprintf(
                '<img src="/assets/bars/balken.png" style="background-color: #%s;height: 12px; width: %dpx;" title="%s" />',
                $color,
                round($width / 100 * (100 / $host->getMaxEps() * $value)),
                'Energieproduktion'
            );
        }

        return $epsBar;
    }

    private function getEpsBarTitleString(PlanetFieldHostInterface $host, int $energyProduction): string
    {
        if ($host instanceof Colony) {
            $changeable = $host->getChangeable();
            $forecast = $changeable->getEps() + $energyProduction;
            if ($changeable->getEps() + $energyProduction < 0) {
                $forecast = 0;
            }
            if ($changeable->getEps() + $energyProduction > $host->getMaxEps()) {
                $forecast = $host->getMaxEps();
            }

            $eps = $changeable->getEps();
        } else {
            $eps = 0;
            $forecast = $energyProduction;
        }

        if ($energyProduction > 0) {
            $energyProduction = sprintf('+%d', $energyProduction);
        }

        return sprintf(
            _('Energie: %d/%d (%s/Runde = %d)'),
            $eps,
            $host->getMaxEps(),
            $energyProduction,
            $forecast
        );
    }
}
