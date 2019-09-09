<?php

namespace Stu\Orm\Entity;

use Alliance;
use AllianceData;

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

    public function getOpponent(): AllianceData;

    public function isWar(): bool;

    public function getPossibleTypes(): array;

    public function offerIsSend(): bool;

    public function getRecipient(): Alliance;

    public function getAlliance(): Alliance;

    public function getTypeDescription(): string;
}