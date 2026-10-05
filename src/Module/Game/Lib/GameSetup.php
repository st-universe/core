<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib;

use request;
use Stu\Component\Game\ModuleEnum;
use Stu\Lib\Component\ComponentRegistrationInterface;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Game\Component\GameComponentEnum;

final class GameSetup implements GameSetupInterface
{
    public function __construct(private ComponentRegistrationInterface $componentRegistration) {}

    #[\Override]
    public function setTemplateAndComponents(string $viewTemplate, ViewControllerContext $context): void
    {
        $context->setTemplateVar('VIEW_TEMPLATE', $viewTemplate);

        if (request::has('switch')) {
            $context->setTemplateFile('html/view/breadcrumbAndView.twig');
        } else {
            $context->setTemplateFile(ModuleEnum::GAME->getTemplate());
            $this->registerComponents();
        }
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
