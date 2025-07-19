<?php

namespace Stu\Component\Spacecraft\System;

use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftSystem;

class SpacecraftSystemWrapper
{
    private SpacecraftSystem $spacecraftSystem;

    public function __construct(
        Spacecraft $spacecraft,
        private SpacecraftSystemTypeEnum $type
    ) {
        $this->spacecraftSystem = $spacecraft->getSpacecraftSystem($type);
    }

    public function get(): SpacecraftSystem
    {
        return $this->spacecraftSystem;
    }

    public function isActivated(): bool
    {
        return $this->spacecraftSystem->getMode()->isActivated();
    }

    public function isHealthy(): bool
    {
        return $this->spacecraftSystem->isHealthy();
    }

    public function getGenericTemplate(): ?string
    {
        return $this->type->getGenericTemplate();
    }

    public function getIcon(): string
    {
        return $this->type->getIcon();
    }

    public function getDescription(): string
    {
        return $this->type->getDescription();
    }

    public function getPreActionJs(): ?string
    {
        return $this->type->isReloadOnActivation()
            ? sprintf('setAjaxMandatory(true);')
            : null;
    }
}
