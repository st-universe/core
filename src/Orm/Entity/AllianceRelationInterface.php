<?php

namespace Stu\Orm\Entity;

interface AllianceRelationInterface
{
    public function getId(): int;

    public function getType(): int;

    public function setType(int $type): AllianceRelationInterface;

    public function getAllianceId(): int;

    public function setAllianceId(int $allianceId): AllianceRelationInterface;

    public function getRecipientId(): int;

    public function setRecipientId(int $recipientId): AllianceRelationInterface;

    public function getDate(): int;

    public function setDate(int $date): AllianceRelationInterface;

    public function isPending(): bool;

    public function getOpponent(): AllianceInterface;

    public function isWar(): bool;

    public function getPossibleTypes(): array;

    public function offerIsSend(): bool;

    public function getRecipient(): AllianceInterface;

    public function getAlliance(): AllianceInterface;

    public function getTypeDescription(): string;
}