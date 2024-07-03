<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use Override;
use Stu\Component\Colony\Shields\ColonyShieldingManagerInterface;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Tal\StatusBarColorEnum;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class ShieldingProvider implements GuiComponentProviderInterface
{
    public function __construct(private ColonyLibFactoryInterface $colonyLibFactory, private PlanetFieldRepositoryInterface $planetFieldRepository)
    {
    }

    #[Override]
    public function setTemplateVariables(
        PlanetFieldHostInterface $host,
        GameControllerInterface $game
    ): void {
        $shieldingManager = $this->colonyLibFactory->createColonyShieldingManager($host);

        if ($shieldingManager->hasShielding()) {
            $game->setTemplateVar(
                'SHIELD_STATUS_BAR',
                $this->buildShieldBar($shieldingManager, $host)
            );

            $game->setTemplateVar('SHIELD_BAR_TITLE_STRING', $this->getShieldBarTitleString($host));
        }
    }

    private function getShieldBarTitleString(PlanetFieldHostInterface $host): string
    {
        return sprintf(
            'Schildstärke: %d/%d',
            $host instanceof ColonyInterface ? $host->getShields() : 0,
            $this->planetFieldRepository->getMaxShieldsOfHost($host)
        );
    }

    private function buildShieldBar(
        ColonyShieldingManagerInterface $colonyShieldingManager,
        PlanetFieldHostInterface $host
    ): string {
        $shieldBar = '';
        $bars = [];
        $width = 360;

        $currentShields = $host instanceof ColonyInterface ? $host->getShields() : 0;
        $maxShields = $colonyShieldingManager->getMaxShielding();

        if ($colonyShieldingManager->isShieldingEnabled()) {
            $bars[StatusBarColorEnum::STATUSBAR_SHIELD_ON] = $currentShields;
        } else {
            $bars[StatusBarColorEnum::STATUSBAR_SHIELD_OFF] = $currentShields;
        }
        $bars[StatusBarColorEnum::STATUSBAR_GREY] = $maxShields - $currentShields;

        foreach ($bars as $color => $value) {
            if ($maxShields < $value) {
                $value = $maxShields;
            }
            if ($value <= 0) {
                continue;
            }
            $shieldBar .= sprintf(
                '<img src="/assets/bars/balken.png" style="background-color: #%s;height: 12px; width: %dpx;" title="%s" />',
                $color,
                round($width / 100 * (100 / $maxShields * $value)),
                'Schildstärke'
            );
        }

        return $shieldBar;
    }
}
