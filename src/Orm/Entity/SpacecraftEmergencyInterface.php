<?php

namespace Stu\Orm\Entity;

interface SpacecraftEmergencyInterface
{
    public function getId(): int;

    public function getShip(): ShipInterface;

    public function setShip(ShipInterface $ship): SpacecraftEmergencyInterface;

    public function getText(): string;

    public function setText(string $text): SpacecraftEmergencyInterface;

    public function getDate(): int;

    public function setDate(int $date): SpacecraftEmergencyInterface;

    public function setDeleted(int $timestamp): SpacecraftEmergencyInterface;

    public function isDeleted(): bool;
}
