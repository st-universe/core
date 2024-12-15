<?php

namespace Stu\Orm\Entity;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;

interface SpacecraftSystemInterface
{
    public function getId(): int;

    public function getSystemType(): SpacecraftSystemTypeEnum;

    public function setSystemType(SpacecraftSystemTypeEnum $type): SpacecraftSystemInterface;

    public function getModuleId(): ?int;

    public function setModuleId(int $moduleId): SpacecraftSystemInterface;

    public function getStatus(): int;

    public function setStatus(int $status): SpacecraftSystemInterface;

    public function getName(): string;

    public function getCssClass(): string;

    public function getMode(): int;

    public function setMode(int $mode): SpacecraftSystemInterface;

    public function getCooldown(): ?int;

    public function setCooldown(int $cooldown): SpacecraftSystemInterface;

    public function getModule(): ?ModuleInterface;

    public function setModule(ModuleInterface $module): SpacecraftSystemInterface;

    public function getSpacecraft(): SpacecraftInterface;

    public function setSpacecraft(SpacecraftInterface $spacecraft): SpacecraftSystemInterface;

    public function getData(): ?string;

    public function setData(string $data): SpacecraftSystemInterface;

    public function determineSystemLevel(): int;
}
