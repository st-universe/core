<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib\Gui;

use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Lib\Component\ComponentRegistrationInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ColonySandbox;

final class ColonyGuiHelper implements ColonyGuiHelperInterface
{
    public function __construct(private ComponentRegistrationInterface $componentRegistration) {}

    #[\Override]
    public function registerMenuComponents(
        ColonyMenuEnum $menu,
        PlanetFieldHostInterface $host,
        GameControllerInterface $game
    ): void {

        foreach ($menu->getNecessaryGuiComponents() as $componentEnum) {
            $this->componentRegistration->registerComponent($componentEnum, $host);
        }

        $game->setTemplateVar('HOST', $host);
        $game->setTemplateVar('CURRENT_MENU', $menu);

        if ($host instanceof ColonySandbox) {
            $game->setTemplateVar('COLONY', $host->getColony());
        } else {
            $game->setTemplateVar('COLONY', $host);
        }
    }

    #[\Override]
    public function registerComponents(
        PlanetFieldHostInterface $host,
        GameControllerInterface $game,
        array $guiComponents
    ): void {
        foreach ($guiComponents as $componentEnum) {
            $this->componentRegistration->registerComponent($componentEnum, $host);
        }
    }
}
