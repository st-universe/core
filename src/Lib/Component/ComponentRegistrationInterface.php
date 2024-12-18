<?php

declare(strict_types=1);

namespace Stu\Lib\Component;

use Doctrine\Common\Collections\Collection;

interface ComponentRegistrationInterface
{
    public function addComponentUpdate(ComponentEnumInterface $componentEnum, bool $isInstantUpdate = true): void;

    public function registerComponent(ComponentEnumInterface $componentEnum, ?object $entity = null): void;

    /** @return Collection<string, RegisteredComponent> */
    public function getRegisteredComponents(): Collection;

    /** @return Collection<string, ComponentUpdate> */
    public function getComponentUpdates(): Collection;

    public function resetComponents(): void;
}
