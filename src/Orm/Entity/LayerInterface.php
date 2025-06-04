<?php

namespace Stu\Orm\Entity;

interface LayerInterface
{
    public function getId(): int;

    public function getName(): string;

    public function getWidth(): int;

    public function getHeight(): int;

    public function isHidden(): bool;

    public function isFinished(): bool;

    public function isEncoded(): bool;

    public function getAward(): ?AwardInterface;

    public function getDescription(): ?string;

    public function setDescription(?string $description): LayerInterface;

    public function isColonizable(): bool;

    public function isNoobzone(): bool;

    public function getSectorsHorizontal(): int;

    public function getSectorsVertical(): int;

    public function getSectorCount(): int;

    public function getSectorId(int $mapCx, int $mapCy): int;
}
