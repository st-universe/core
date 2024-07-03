<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View;

use Override;
use RuntimeException;
use Stu\Component\Game\ModuleViewEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Game\Lib\View\Provider\ViewComponentProviderInterface;

final class ViewComponentLoader implements ViewComponentLoaderInterface
{
    /** @param array<int, ViewComponentProviderInterface> $viewComponentProviders */
    public function __construct(private array $viewComponentProviders)
    {
    }

    #[Override]
    public function registerViewComponents(
        ModuleViewEnum $view,
        GameControllerInterface $game
    ): void {

        if (!array_key_exists($view->value, $this->viewComponentProviders)) {
            throw new RuntimeException(sprintf('viewComponentProvider with follwing id does not exist: %s', $view->value));
        }

        $game->appendNavigationPart(
            $view->getPhpPage(),
            $view->getTitle(),
        );

        $componentProvider = $this->viewComponentProviders[$view->value];
        $componentProvider->setTemplateVariables($game);

        $game->setTemplateVar('CURRENT_VIEW', $view);
    }
}
