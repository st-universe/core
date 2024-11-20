<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\Component;

use Override;
use RuntimeException;
use Stu\Component\Game\GameEnum;
use Stu\Component\Game\ModuleViewEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\Render\Fragments\RenderFragmentInterface;
use Stu\Module\Game\View\ShowComponent\ShowComponent;
use Stu\Module\Twig\TwigPageInterface;

final class ComponentLoader implements ComponentLoaderInterface
{
    /** @var array<string, ComponentUpdate> */
    private array $componentUpdates = [];

    /** @var array<ComponentEnum> */
    private array $neededComponents = [];

    /** @param array<int, RenderFragmentInterface> $componentProviders */
    public function __construct(
        private TwigPageInterface $twigPage,
        private array $componentProviders
    ) {}

    #[Override]
    public function addComponentUpdate(ComponentEnum $component, bool $isInstantUpdate = true): void
    {
        if (!array_key_exists($component->value, $this->componentUpdates)) {
            $this->componentUpdates[$component->value] = new ComponentUpdate($component, $isInstantUpdate);
        }
    }

    /**
     * Adds the execute javascript after render.
     */
    #[Override]
    public function loadComponentUpdates(GameControllerInterface $game): void
    {
        foreach ($this->componentUpdates as $update) {

            $component = $update->getComponent();
            $isInstantUpdate = $update->isInstantUpdate();

            if ($isInstantUpdate) {
                $this->addExecuteJs(
                    $component->value,
                    '',
                    $game
                );
            }

            $refreshInterval = $component->getRefreshIntervalInSeconds();

            if (!$isInstantUpdate || $refreshInterval !== null) {
                $this->addExecuteJs(
                    $component->value,
                    $refreshInterval === null ? '' : sprintf(', %d', $refreshInterval * 1000),
                    $game
                );
            }
        }
    }

    private function addExecuteJs(string $componentValue, string $refreshParam, GameControllerInterface $game): void
    {
        $game->addExecuteJS(sprintf(
            "updateComponent('navlet_%s', '/%s?%s=1&component=%s'%s);",
            $componentValue,
            ModuleViewEnum::GAME->getPhpPage(),
            ShowComponent::VIEW_IDENTIFIER,
            $componentValue,
            $refreshParam
        ), GameEnum::JS_EXECUTION_AFTER_RENDER);
    }

    #[Override]
    public function registerComponent(ComponentEnum $component): void
    {
        if (!in_array($component, $this->neededComponents)) {
            $this->neededComponents[] = $component;
        }
    }

    #[Override]
    public function loadRegisteredComponents(GameControllerInterface $game): void
    {

        foreach ($this->neededComponents as $component) {
            if (!array_key_exists($component->value, $this->componentProviders)) {
                throw new RuntimeException(sprintf('componentProvider with follwing id does not exist: %s', $component->value));
            }

            $componentProvider = $this->componentProviders[$component->value];
            $componentProvider->render($game->getUser(), $this->twigPage, $game);
        }
    }
}
