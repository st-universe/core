<?php

declare(strict_types=1);

namespace Stu\Lib\Component;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Override;

final class ComponentRegistration implements ComponentRegistrationInterface
{
    /** @var Collection<string, ComponentUpdate> */
    private Collection $componentUpdates;

    /** @var Collection<string, ComponentEnumInterface> */
    private Collection $registeredComponents;

    public function __construct()
    {
        $this->componentUpdates = new ArrayCollection();
        $this->registeredComponents = new ArrayCollection();
    }

    #[Override]
    public function addComponentUpdate(ComponentEnumInterface $componentEnum, bool $isInstantUpdate = true): void
    {
        $id = $this->getId($componentEnum);
        if (!$this->componentUpdates->containsKey($id)) {
            $this->componentUpdates[$id] = new ComponentUpdate($componentEnum, $isInstantUpdate);
        }
    }

    #[Override]
    public function registerComponent(ComponentEnumInterface $componentEnum): void
    {
        $id = $this->getId($componentEnum);
        if (!$this->registeredComponents->containsKey($id)) {
            $this->registeredComponents->set($id, $componentEnum);
        }
    }

    #[Override]
    public function getRegisteredComponents(): Collection
    {
        return $this->registeredComponents;
    }

    #[Override]
    public function getComponentUpdates(): Collection
    {
        return $this->componentUpdates;
    }

    #[Override]
    public function resetComponents(): void
    {
        $this->componentUpdates->clear();
        $this->registeredComponents->clear();
    }

    private function getId(ComponentEnumInterface $componentEnum): string
    {
        return strtoupper(sprintf(
            '%s_%s',
            $componentEnum->getModuleView()->value,
            $componentEnum->getValue()
        ));
    }
}
