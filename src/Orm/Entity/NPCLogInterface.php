<?php

namespace Stu\Orm\Entity;

interface NPCLogInterface
{
    public function getId(): int;

    public function getText(): string;

    public function setText(string $text): NPCLogInterface;

    public function getDate(): int;

    public function setDate(int $date): NPCLogInterface;

    public function getSourceUserId(): ?int;

    public function setSourceUserId(int $userId): NPCLogInterface;

    public function getFactionId(): ?int;

    public function setFactionId(?int $factionId): NPCLogInterface;
}
