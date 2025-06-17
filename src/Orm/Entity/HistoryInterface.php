<?php

namespace Stu\Orm\Entity;

use Stu\Component\History\HistoryTypeEnum;

interface HistoryInterface
{
    public function getId(): int;

    public function getText(): string;

    public function setText(string $text): HistoryInterface;

    public function getDate(): int;

    public function setDate(int $date): HistoryInterface;

    public function getType(): HistoryTypeEnum;

    public function setType(HistoryTypeEnum $type): HistoryInterface;

    public function getSourceUserId(): ?int;

    public function setSourceUserId(int $userId): HistoryInterface;

    public function getTargetUserId(): ?int;

    public function setTargetUserId(int $userId): HistoryInterface;
}
