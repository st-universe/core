<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib;

use Override;
use Stu\Component\Game\ModuleViewEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Game\Lib\Component\ComponentEnum;
use Stu\Module\Game\Lib\Component\ComponentLoaderInterface;

final class GameSetup implements GameSetupInterface
{
    public function __construct(private ComponentLoaderInterface $componentLoader)
    {
    }

    #[Override]
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
