<?php

namespace Stu\Module\Ship\Lib\Battle;

interface FightMessageInterface
{
    public function getRecipientId(): ?int;

    public function getMessage(): array;

    public function add(?string $msg): void;

    public function addMessageMerge(array $msg): void;
}
