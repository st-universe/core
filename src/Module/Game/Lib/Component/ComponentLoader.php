<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\Component;

use RuntimeException;
use Stu\Component\Game\GameEnum;
use Stu\Component\Game\ModuleViewEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\Render\Fragments\RenderFragmentInterface;
use Stu\Module\Game\View\ShowComponent\ShowComponent;
use Stu\Module\Twig\TwigPageInterface;

final class ComponentLoader implements ComponentLoaderInterface
{
    /** @var array<int, RenderFragmentInterface> */
    private array $componentProviders;

    /** @var array<ComponentEnum> */
    private array $componentUpdates = [];

    /** @var array<ComponentEnum> */
    private array $neededComponents = [];

    /** @param array<int, RenderFragmentInterface> $componentProviders */
    public function __construct(
        array $componentProviders
    ) {
        $this->componentProviders = $componentProviders;
    }


    public function addComponentUpdate(ComponentEnum $component): void
    {
        if (!in_array($component, $this->componentUpdates)) {
            $this->componentUpdates[] = $component;
        }
    }

    /** 
     * Adds the execute javascript after render.
     */
    public function loadComponentUpdates(GameControllerInterface $game): void
    {
        foreach ($this->componentUpdates as $component) {
            $refreshInterval = $component->getRefreshIntervalInSeconds();

            $game->addExecuteJS(sprintf(
                "updateComponent('navlet_%s', '/%s?%s=1&component=%s'%s);",
                $component->value,
                ModuleViewEnum::GAME->getPhpPage(),
                ShowComponent::VIEW_IDENTIFIER,
                $component->value,
                $refreshInterval === null ? '' : sprintf(', %d', $refreshInterval * 1000)
            ), GameEnum::JS_EXECUTION_AFTER_RENDER);
        }
    }

    public function registerComponent(ComponentEnum $component): void
    {
        if (!in_array($component, $this->neededComponents)) {
            $this->neededComponents[] = $component;
        }
    }

    public function loadRegisteredComponents(
        TwigPageInterface $twigPage,
        GameControllerInterface $game
    ): void {

        foreach ($this->neededComponents as $component) {
            if (!array_key_exists($component->value, $this->componentProviders)) {
                throw new RuntimeException(sprintf('componentProvider with follwing id does not exist: %s', $component->value));
            }

            $componentProvider = $this->componentProviders[$component->value];
            $componentProvider->render($game->getUser(), $twigPage, $game);
        }
    }
}
