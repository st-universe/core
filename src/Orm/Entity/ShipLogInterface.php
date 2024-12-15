<?php

namespace Stu\Orm\Entity;

interface ShipLogInterface
{
    public function getId(): int;

    public function setSpacecraft(SpacecraftInterface $spacecraft): ShipLogInterface;

    public function getText(): string;

    public function setText(string $text): ShipLogInterface;

    public function getDate(): int;

    public function setDate(int $date): ShipLogInterface;

    public function setDeleted(int $timestamp): ShipLogInterface;

    public function isDeleted(): bool;
}
