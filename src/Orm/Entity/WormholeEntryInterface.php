<?php

namespace Stu\Orm\Entity;

interface WormholeEntryInterface
{
    public function getId(): int;

    public function getMap(): MapInterface;

    public function getSystem(): StarSystemInterface;

    public function getSystemMap(): StarSystemMapInterface;

    public function getType(): int;

    public function setLastUsed(int $lastUsed): WormholeEntryInterface;

    public function isUsable(): bool;
}
