<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use Stu\Component\Colony\Shields\ColonyShieldingManagerInterface;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Template\StatusBarColorEnum;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class ShieldingProvider implements PlanetFieldHostComponentInterface
{
    public function __construct(private ColonyLibFactoryInterface $colonyLibFactory, private PlanetFieldRepositoryInterface $planetFieldRepository) {}

    #[\Override]
    public function setTemplateVariables(
        $entity,
        GameControllerInterface $game
    ): void {
        $shieldingManager = $this->colonyLibFactory->createColonyShieldingManager($entity);

        if ($shieldingManager->hasShielding()) {
            $game->setTemplateVar(
                'SHIELD_STATUS_BAR',
                $this->buildShieldBar($shieldingManager, $entity)
            );

            $game->setTemplateVar('SHIELD_BAR_TITLE_STRING', $this->getShieldBarTitleString($entity));
        }
    }

    private function getShieldBarTitleString(PlanetFieldHostInterface $host): string
    {
        return sprintf(
            'Schildstärke: %d/%d',
            $host instanceof Colony ? $host->getChangeable()->getShields() : 0,
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

        $currentShields = $host instanceof Colony ? $host->getChangeable()->getShields() : 0;
        $maxShields = $colonyShieldingManager->getMaxShielding();

        if ($colonyShieldingManager->isShieldingEnabled()) {
            $bars[StatusBarColorEnum::SHIELD_ON->value] = $currentShields;
        } else {
            $bars[StatusBarColorEnum::SHIELD_OFF->value] = $currentShields;
        }
        $bars[StatusBarColorEnum::GREY->value] = $maxShields - $currentShields;

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
