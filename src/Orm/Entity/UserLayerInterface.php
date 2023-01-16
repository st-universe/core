<?php

namespace Stu\Orm\Entity;

interface UserLayerInterface
{
    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): UserLayerInterface;

    public function getLayer(): LayerInterface;

    public function setLayer(LayerInterface $layer): UserLayerInterface;

    public function getMappingType(): int;

    public function setMappingType(int $mappingType): UserLayerInterface;
}
