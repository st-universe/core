<?php

namespace Stu\Orm\Entity;

interface BuildplanHangarInterface
{
    public function getId(): int;

    public function getBuildplanId(): int;

    public function setBuildplanId(int $buildplanId): BuildplanHangarInterface;

    public function getDefaultTorpedoTypeId(): int;

    public function setDefaultTorpedoTypeId(int $defaultTorpedoTypeId): BuildplanHangarInterface;

    public function getDefaultTorpedoType(): ?TorpedoTypeInterface;

    public function getBuildplan(): SpacecraftBuildplanInterface;

    public function setStartEnergyCosts(int $startEnergyCosts): BuildplanHangarInterface;

    public function getStartEnergyCosts(): int;

    public function getRumpId(): int;
}
