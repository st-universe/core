<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib;

use Stu\Component\Game\ModuleViewEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Game\Lib\Component\ComponentEnum;
use Stu\Module\Game\Lib\Component\ComponentLoaderInterface;

final class GameSetup implements GameSetupInterface
{
    private ComponentLoaderInterface $componentLoader;

    public function __construct(
        ComponentLoaderInterface $componentLoader
    ) {
        $this->componentLoader = $componentLoader;
    }

    public function setTemplateAndComponents(string $viewTemplate, GameControllerInterface $game): void
    {
        $game->setTemplateFile(ModuleViewEnum::GAME->getTemplate());
        $game->setTemplateVar('VIEW_TEMPLATE', $viewTemplate);

        $this->registerComponents();
    }

    private function registerComponents(): void
    {
        foreach (ComponentEnum::cases() as $component) {
            $this->componentLoader->registerComponent($component);

            if ($component->getRefreshIntervalInSeconds() !== null) {
                $this->componentLoader->addComponentUpdate($component, false);
            }
        }
    }
}
