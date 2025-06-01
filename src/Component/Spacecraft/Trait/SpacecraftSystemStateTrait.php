<?php

namespace Stu\Component\Spacecraft\Trait;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;

trait SpacecraftSystemStateTrait
{
    use SpacecraftTrait;
    use HasSpacecraftSystemTrait;

    public function isShielded(): bool
    {
        return $this->getSystemState(SpacecraftSystemTypeEnum::SHIELDS);
    }

    public function getNbs(): bool
    {
        return $this->getSystemState(SpacecraftSystemTypeEnum::NBS);
    }

    public function getLss(): bool
    {
        return $this->getSystemState(SpacecraftSystemTypeEnum::LSS);
    }

    public function isCloaked(): bool
    {
        return $this->getSystemState(SpacecraftSystemTypeEnum::CLOAK);
    }

    public function getImpulseState(): bool
    {
        return $this->getSystemState(SpacecraftSystemTypeEnum::IMPULSEDRIVE);
    }

    public function getWarpDriveState(): bool
    {
        return $this->getSystemState(SpacecraftSystemTypeEnum::WARPDRIVE);
    }

    public function getPhaserState(): bool
    {
        return $this->getSystemState(SpacecraftSystemTypeEnum::PHASER);
    }

    public function getTorpedoState(): bool
    {
        return $this->getSystemState(SpacecraftSystemTypeEnum::TORPEDO);
    }

    public function getTachyonState(): bool
    {
        return $this->getSystemState(SpacecraftSystemTypeEnum::TACHYON_SCANNER);
    }

    public function getSubspaceState(): bool
    {
        return $this->getSystemState(SpacecraftSystemTypeEnum::SUBSPACE_SCANNER);
    }

    public function getRPGModuleState(): bool
    {
        return $this->getSystemState(SpacecraftSystemTypeEnum::RPG_MODULE);
    }

    public function hasActiveWeapon(): bool
    {
        return $this->getPhaserState() || $this->getTorpedoState();
    }

    public function getSystemState(SpacecraftSystemTypeEnum $type): bool
    {
        if (!$this->hasSpacecraftSystem($type)) {
            return false;
        }

        return $this->getSpacecraftSystem($type)->getMode()->isActivated();
    }
}
