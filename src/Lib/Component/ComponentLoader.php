<?php

declare(strict_types=1);

namespace Stu\Lib\Component;

use OutOfBoundsException;
use RuntimeException;
use Stu\Component\Game\JavascriptExecutionTypeEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Config\Init;
use Stu\Module\Control\JavascriptExecutionInterface;
use Stu\Module\Game\View\ShowComponent\ShowComponent;
use Stu\Module\Template\TemplateInterface;
use Stu\Orm\Entity\User;

final class ComponentLoader implements ComponentLoaderInterface
{
    /** @var array<ComponentEnumInterface> */
    private array $registeredStubs = [];

    public function __construct(
        private readonly ComponentRegistrationInterface $componentRegistration,
        private readonly JavascriptExecutionInterface $javascriptExecution
    ) {}

    /**
     * Adds the execute javascript after render.
     */
    #[\Override]
    public function loadComponentUpdates(): void
    {
        foreach ($this->componentRegistration->getComponentUpdates() as $id => $componentUpdate) {

            $isInstantUpdate = $componentUpdate->isInstantUpdate();
            if ($isInstantUpdate) {
                $this->addExecuteJs(
                    $id,
                    $componentUpdate,
                    ''
                );
                continue;
            }

            $refreshInterval = $componentUpdate->getComponentEnum()->getRefreshIntervalInSeconds();
            if ($refreshInterval !== null) {
                $this->addExecuteJs(
                    $id,
                    $componentUpdate,
                    sprintf(', %d', $refreshInterval * 1000)
                );
            }
        }
    }

    private function addExecuteJs(
        string $id,
        ComponentUpdate $componentUpdate,
        string $refreshParam
    ): void {

        $this->javascriptExecution
            ->addExecuteJS(sprintf(
                "updateComponent('%s', '/%s?%s=1&component=%s%s'%s);",
                $id,
                ModuleEnum::GAME->getPhpPage(),
                ShowComponent::VIEW_IDENTIFIER,
                $id,
                $componentUpdate->getComponentParameters() ?? '',
                $refreshParam
            ), JavascriptExecutionTypeEnum::AFTER_RENDER);
    }

    #[\Override]
    public function loadRegisteredComponents(User $user, TemplateInterface $template): void
    {
        foreach ($this->componentRegistration->getRegisteredComponents() as $id => $registeredComponent) {

            $componentEnum = $registeredComponent->componentEnum;
            $isStubbed = in_array($componentEnum, $this->registeredStubs);

            if (!$isStubbed && $componentEnum->hasTemplateVariables()) {
                $moduleId = strtoupper($componentEnum->getModuleView()->value);

                /** @var array<string, ComponentInterface> */
                $moduleComponents = Init::getContainer()
                    ->get(sprintf('%s_COMPONENTS', $moduleId));

                if (!array_key_exists($componentEnum->getValue(), $moduleComponents)) {
                    throw new OutOfBoundsException(sprintf('component with follwing id does not exist: %s', $id));
                }

                $component = $moduleComponents[$componentEnum->getValue()];

                if ($component instanceof EntityComponentInterface) {
                    $entity = $registeredComponent->entity;
                    if ($entity === null) {
                        throw new RuntimeException('this should not happen');
                    }
                    $component->setTemplateVariables($entity, $template, $user);
                } else {
                    $component->setTemplateVariables($user, $template);
                }
            }

            $template->setTemplateVar($id, ['id' => $id, 'template' => $isStubbed ? null : $componentEnum->getTemplate()]);
        }
    }

    #[\Override]
    public function registerStubbedComponent(ComponentEnumInterface $componentEnum): ComponentLoaderInterface
    {
        $this->registeredStubs[] = $componentEnum;

        return $this;
    }

    #[\Override]
    public function resetStubbedComponents(): void
    {
        $this->registeredStubs = [];
    }
}
