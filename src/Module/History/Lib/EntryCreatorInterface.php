<?php

namespace Stu\Module\History\Lib;

interface EntryCreatorInterface
{
    public function addShipEntry(string $text, int $userId = USER_NOONE): void;

    public function addAllianceEntry(string $text, int $userId = USER_NOONE): void;
}