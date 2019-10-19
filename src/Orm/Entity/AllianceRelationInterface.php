<?php

namespace Stu\Orm\Entity;

interface AllianceRelationInterface
{
    public function getId(): int;

    public function getType(): int;

    public function setType(int $type): AllianceRelationInterface;

    public function getAllianceId(): int;

    public function getOpponentId(): int;

    public function getDate(): int;

    public function setDate(int $date): AllianceRelationInterface;

    public function isPending(): bool;

    public function isWar(): bool;

    public function getPossibleTypes(): array;

    public function getAlliance(): AllianceInterface;

    public function setAlliance(AllianceInterface $alliance): AllianceRelationInterface;

    public function getOpponent(): AllianceInterface;

    public function setOpponent(AllianceInterface $opponent): AllianceRelationInterface;

    public function getTypeDescription(): string;
}
