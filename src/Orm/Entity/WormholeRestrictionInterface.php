<?php

namespace Stu\Orm\Entity;

interface WormholeRestrictionInterface
{
    public function getId(): int;

    public function getWormholeEntry(): WormholeEntryInterface;

    public function setWormholeEntry(WormholeEntryInterface $wormholeEntry): WormholeRestrictionInterface;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): WormholeRestrictionInterface;

    public function getMode(): ?int;

    public function setMode(?int $mode): WormholeRestrictionInterface;
}
