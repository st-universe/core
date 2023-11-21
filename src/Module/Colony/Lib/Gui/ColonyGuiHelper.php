<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib\Gui;

use RuntimeException;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Module\Colony\Lib\Gui\Component\GuiComponentProviderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ColonySandboxInterface;

final class ColonyGuiHelper implements ColonyGuiHelperInterface
{
    /** @var array<int, GuiComponentProviderInterface> */
    private array $guiComponentProviders;

    /** @param array<int, GuiComponentProviderInterface> $guiComponentProviders */
    public function __construct(array $guiComponentProviders)
    {
        $this->guiComponentProviders = $guiComponentProviders;
    }

    public function registerMenuComponents(
        ColonyMenuEnum $menu,
        PlanetFieldHostInterface $host,
        GameControllerInterface $game
    ): void {

        foreach ($menu->getNecessaryGuiComponents() as $component) {
            $this->processComponent($component, $host, $game);
        }

        $game->setTemplateVar('HOST', $host);
        $game->setTemplateVar('CURRENT_MENU', $menu);

        if ($host instanceof ColonyInterface) {
            $game->setTemplateVar('COLONY', $host);
            $game->setTemplateVar('FORM_ACTION', 'colony.php');
        }
        if ($host instanceof ColonySandboxInterface) {
            $game->setTemplateVar('COLONY', $host->getColony());
            $game->setTemplateVar('FORM_ACTION', '/admin/index.php');
        }
    }

    public function registerComponents(
        PlanetFieldHostInterface $host,
        GameControllerInterface $game,
        array $guiComponents
    ): void {
        foreach ($guiComponents as $component) {
            $this->processComponent($component, $host, $game);
        }
    }

    private function processComponent(
        GuiComponentEnum $guiComponent,
        PlanetFieldHostInterface $host,
        GameControllerInterface $game,
    ): void {
        if (!array_key_exists($guiComponent->value, $this->guiComponentProviders)) {
            throw new RuntimeException(sprintf('guiComponentProvider with follwing id does not exist: %d', $guiComponent->value));
        }

        $componentProvider = $this->guiComponentProviders[$guiComponent->value];
        $componentProvider->setTemplateVariables($host, $game);
    }
}
