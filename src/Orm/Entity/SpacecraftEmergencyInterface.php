<?php

namespace Stu\Orm\Entity;

interface SpacecraftEmergencyInterface
{
    public function getId(): int;

    public function getSpacecraft(): SpacecraftInterface;

    public function setSpacecraft(SpacecraftInterface $spacecraft): SpacecraftEmergencyInterface;

    public function getText(): string;

    public function setText(string $text): SpacecraftEmergencyInterface;

    public function getDate(): int;

    public function setDate(int $date): SpacecraftEmergencyInterface;

    public function setDeleted(int $timestamp): SpacecraftEmergencyInterface;

    public function isDeleted(): bool;
}
