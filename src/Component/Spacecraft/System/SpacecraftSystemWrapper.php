<?php

namespace Stu\Component\Spacecraft\System;

use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\SpacecraftSystemInterface;

class SpacecraftSystemWrapper
{
    private SpacecraftSystemInterface $spacecraftSystem;

    public function __construct(
        SpacecraftInterface $spacecraft,
        private SpacecraftSystemTypeEnum $type
    ) {
        $this->spacecraftSystem = $spacecraft->getSpacecraftSystem($type);
    }

    public function get(): SpacecraftSystemInterface
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
}
