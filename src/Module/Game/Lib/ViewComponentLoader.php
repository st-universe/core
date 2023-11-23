<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib;

use RuntimeException;
use Stu\Component\Game\ModuleViewEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Game\Lib\Component\ViewComponentProviderInterface;

final class ViewComponentLoader implements ViewComponentLoaderInterface
{
    /** @var array<int, ViewComponentProviderInterface> */
    private array $viewComponentProviders;

    /** @param array<int, ViewComponentProviderInterface> $viewComponentProviders */
    public function __construct(
        array $viewComponentProviders
    ) {
        $this->viewComponentProviders = $viewComponentProviders;
    }

    public function registerViewComponents(
        ModuleViewEnum $view,
        GameControllerInterface $game
    ): void {

        if (!array_key_exists($view->value, $this->viewComponentProviders)) {
            throw new RuntimeException(sprintf('viewComponentProvider with follwing id does not exist: %s', $view->value));
        }

        $componentProvider = $this->viewComponentProviders[$view->value];
        $componentProvider->setTemplateVariables($game);

        $game->setTemplateVar('CURRENT_VIEW', $view);
    }
}
