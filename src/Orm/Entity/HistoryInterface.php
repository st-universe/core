<?php

namespace Stu\Orm\Entity;

interface HistoryInterface
{
    public function getId(): int;

    public function getText(): string;

    public function setText(string $text): HistoryInterface;

    public function getDate(): int;

    public function setDate(int $date): HistoryInterface;

    public function getType(): int;

    public function setType(int $type): HistoryInterface;

    public function getUserId(): int;

    public function setUserId(int $userId): HistoryInterface;
}