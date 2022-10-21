<?php

namespace Stu\Orm\Entity;

interface WormholeEntryInterface
{
    public function getId(): int;

    public function getMap(): MapInterface;

    public function getSystem(): StarSystemInterface;

    public function getSystemMap(): StarSystemMapInterface;
}
