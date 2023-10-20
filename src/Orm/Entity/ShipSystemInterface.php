<?php

namespace Stu\Orm\Entity;

use Stu\Component\Ship\System\ShipSystemTypeEnum;

interface ShipSystemInterface
{
    public function getId(): int;

    public function getSystemType(): ShipSystemTypeEnum;

    public function setSystemType(ShipSystemTypeEnum $type): ShipSystemInterface;

    public function getModuleId(): int;

    public function setModuleId(int $moduleId): ShipSystemInterface;

    public function getStatus(): int;

    public function setStatus(int $status): ShipSystemInterface;

    public function getName(): string;

    public function getCssClass(): string;

    public function getMode(): int;

    public function setMode(int $mode): ShipSystemInterface;

    public function getCooldown(): ?int;

    public function setCooldown(int $cooldown): ShipSystemInterface;

    public function getModule(): ?ModuleInterface;

    public function setModule(ModuleInterface $module): ShipSystemInterface;

    public function getShip(): ShipInterface;

    public function setShip(ShipInterface $ship): ShipSystemInterface;

    public function getData(): ?string;

    public function setData(string $data): ShipSystemInterface;

    public function determineSystemLevel(): int;
}
