<?php

namespace Stu\Orm\Entity;

interface TorpedoHullInterface
{
    public function getId(): int;

    public function getModuleId(): int;

    public function setModuleId(int $moduleId): TorpedoHullInterface;

    public function getTorpedoType(): int;

    public function setTorpedoType(int $torpedoType): TorpedoHullInterface;

    public function getModificator(): int;

    public function setModificator(int $Modificator): TorpedoHullInterface;

    public function getTorpedo(): ?TorpedoTypeInterface;

    public function getModule(): ?ModuleInterface;

    public function calculateGradientColor(): string;

    public function hexToRgb(string $color): array;

    public function calculateGradientRgb(string $rgb1, string $rgb2, float $percent): array;

    public function rgbToHex(string $rgb): string;
}