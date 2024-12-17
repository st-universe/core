<?php

declare(strict_types=1);

namespace Stu\Lib\Component;

use Override;
use RuntimeException;
use Stu\Component\Game\GameEnum;
use Stu\Config\Init;
use Stu\Lib\Component\ComponentInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Game\View\ShowComponent\ShowComponent;

final class ComponentLoader implements ComponentLoaderInterface
{
    /** @var array<ComponentEnumInterface> */
    private array $registeredStubs = [];

    public function __construct(
        private ComponentRegistrationInterface $componentRegistration,
        private ComponentRendererInterface $componentRenderer,
    ) {}

    /**
     * Adds the execute javascript after render.
     */
    #[Override]
    public function loadComponentUpdates(GameControllerInterface $game): void
    {
        foreach ($this->componentRegistration->getComponentUpdates() as $id => $update) {

            $componentEnum = $update->getComponentEnum();
            $isInstantUpdate = $update->isInstantUpdate();

            if ($isInstantUpdate) {
                $this->addExecuteJs(
                    $id,
                    $componentEnum,
                    '',
                    $game
                );
            }

            $refreshInterval = $componentEnum->getRefreshIntervalInSeconds();

            if (!$isInstantUpdate || $refreshInterval !== null) {
                $this->addExecuteJs(
                    $id,
                    $componentEnum,
                    $refreshInterval === null ? '' : sprintf(', %d', $refreshInterval * 1000),
                    $game
                );
            }
        }
    }

    private function addExecuteJs(string $id, ComponentEnumInterface $componentEnum, string $refreshParam, GameControllerInterface $game): void
    {
        $moduleView = $componentEnum->getModuleView();

        $game->addExecuteJS(sprintf(
            "updateComponent('%s', '/%s?%s=1&id=%s'%s);",
            $id,
            $moduleView->getPhpPage(),
            ShowComponent::VIEW_IDENTIFIER,
            $id,
            $refreshParam
        ), GameEnum::JS_EXECUTION_AFTER_RENDER);
    }

    #[Override]
    public function loadRegisteredComponents(GameControllerInterface $game): void
    {
        foreach ($this->componentRegistration->getRegisteredComponents() as $id => $componentEnum) {

            $isStubbed = in_array($componentEnum, $this->registeredStubs);

            if (!$isStubbed && $componentEnum->hasTemplateVariables()) {
                $moduleId = strtoupper($componentEnum->getModuleView()->value);

                /** @var array<string, ComponentInterface> */
                $moduleComponents = Init::getContainer()
                    ->get(sprintf('%s_COMPONENTS', $moduleId));

                if (!array_key_exists($componentEnum->getValue(), $moduleComponents)) {
                    throw new RuntimeException(sprintf('component with follwing id does not exist: %s', $id));
                }

                $component = $moduleComponents[$componentEnum->getValue()];
                $this->componentRenderer->renderComponent($component, $game);
            }

            $game->setTemplateVar($id, ['id' => $id, 'template' => $isStubbed ? null : $componentEnum->getTemplate()]);
        }
    }

    #[Override]
    public function registerStubbedComponent(ComponentEnumInterface $componentEnum): ComponentLoaderInterface
    {
        $this->registeredStubs[] = $componentEnum;

        return $this;
    }

    #[Override]
    public function resetStubbedComponents(): void
    {
        $this->registeredStubs = [];
    }
}
