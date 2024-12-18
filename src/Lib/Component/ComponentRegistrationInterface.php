<?php

declare(strict_types=1);

namespace Stu\Lib\Component;

use Doctrine\Common\Collections\Collection;

interface ComponentRegistrationInterface
{
    public function addComponentUpdate(
        ComponentEnumInterface $componentEnum,
        ?EntityWithComponentsInterface $entity = null,
        bool $isInstantUpdate = true
    ): ComponentRegistrationInterface;

    public function registerComponent(ComponentEnumInterface $componentEnum, ?EntityWithComponentsInterface $entity = null): ComponentRegistrationInterface;

    /** @return Collection<string, RegisteredComponent> */
    public function getRegisteredComponents(): Collection;

    /** @return Collection<string, ComponentUpdate> */
    public function getComponentUpdates(): Collection;

    public function resetComponents(): void;
}
