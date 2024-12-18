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
        private ComponentRegistrationInterface $componentRegistration
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
                continue;
            }

            $refreshInterval = $componentEnum->getRefreshIntervalInSeconds();
            if ($refreshInterval !== null) {
                $this->addExecuteJs(
                    $id,
                    $componentEnum,
                    sprintf(', %d', $refreshInterval * 1000),
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
        foreach ($this->componentRegistration->getRegisteredComponents() as $id => $registeredComponent) {

            $componentEnum = $registeredComponent->componentEnum;
            $isStubbed = in_array($componentEnum, $this->registeredStubs);

            if (!$isStubbed && $componentEnum->hasTemplateVariables()) {
                $moduleId = strtoupper($componentEnum->getModuleView()->value);

                /** @var array<string, ComponentInterface|EntityComponentInterface<object>> */
                $moduleComponents = Init::getContainer()
                    ->get(sprintf('%s_COMPONENTS', $moduleId));

                if (!array_key_exists($componentEnum->getValue(), $moduleComponents)) {
                    throw new RuntimeException(sprintf('component with follwing id does not exist: %s', $id));
                }

                $component = $moduleComponents[$componentEnum->getValue()];

                if ($component instanceof ComponentInterface) {
                    $component->setTemplateVariables($game);
                }
                if ($component instanceof EntityComponentInterface) {
                    $entity = $registeredComponent->entity;
                    if ($entity === null) {
                        throw new RuntimeException('this should not happen');
                    }
                    $component->setTemplateVariables($entity, $game);
                }
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
