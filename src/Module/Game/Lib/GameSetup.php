<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib;

use Override;
use Stu\Component\Game\ModuleEnum;
use Stu\Lib\Component\ComponentRegistrationInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Game\Component\GameComponentEnum;

final class GameSetup implements GameSetupInterface
{
    public function __construct(private ComponentRegistrationInterface $componentRegistration) {}

    #[Override]
    public function setTemplateAndComponents(string $viewTemplate, GameControllerInterface $game): void
    {
        $game->setTemplateFile(ModuleEnum::GAME->getTemplate());
        $game->setTemplateVar('VIEW_TEMPLATE', $viewTemplate);

        $this->registerComponents();
    }

    private function registerComponents(): void
    {
        foreach (GameComponentEnum::cases() as $componentEnum) {
            $this->componentRegistration->registerComponent($componentEnum);

            if ($componentEnum->getRefreshIntervalInSeconds() !== null) {
                $this->componentRegistration->addComponentUpdate($componentEnum, null, false);
            }
        }
    }
}
