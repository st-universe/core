<?php

namespace Stu\Orm\Entity;

interface WormholeEntryInterface
{
    public function getId(): int;

    public function getMap(): MapInterface;

    public function getSystem(): StarSystemInterface;

    public function getSystemMap(): StarSystemMapInterface;

    public function setLastUsed(int $lastUsed): WormholeEntryInterface;

    public function isUsable(LocationInterface $location): bool;

    /**
     * @return iterable<WormholeRestriction>
     */
    public function getRestrictions(): iterable;
}
