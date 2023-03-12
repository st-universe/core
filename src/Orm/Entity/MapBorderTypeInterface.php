<?php

namespace Stu\Orm\Entity;

interface MapBorderTypeInterface
{
    public function getId(): int;

    public function getFactionId(): int;

    public function setFactionId(int $factionId): MapBorderTypeInterface;

    public function getColor(): string;

    public function setColor(string $color): MapBorderTypeInterface;

    public function getDescription(): string;

    public function setDescription(string $description): MapBorderTypeInterface;
}