<?php

namespace Stu\Module\History\Lib;

use Stu\Module\PlayerSetting\Lib\UserEnum;

interface EntryCreatorInterface
{
    public function addShipEntry(
        string $text,
        int $sourceUserId = UserEnum::USER_NOONE,
        int $targetUserId = UserEnum::USER_NOONE
    ): void;

    public function addStationEntry(
        string $text,
        int $sourceUserId = UserEnum::USER_NOONE,
        int $targetUserId = UserEnum::USER_NOONE
    ): void;

    public function addColonyEntry(
        string $text,
        int $sourceUserId = UserEnum::USER_NOONE,
        int $targetUserId = UserEnum::USER_NOONE
    ): void;

    public function addAllianceEntry(
        string $text,
        int $sourceUserId = UserEnum::USER_NOONE,
        int $targetUserId = UserEnum::USER_NOONE
    ): void;

    public function addOtherEntry(
        string $text,
        int $sourceUserId = UserEnum::USER_NOONE,
        int $targetUserId = UserEnum::USER_NOONE
    ): void;
}