<?php

namespace Stu\Orm\Entity;

interface TorpedoStorageInterface
{
    public function getId(): int;

    public function getSpacecraft(): SpacecraftInterface;

    public function setSpacecraft(SpacecraftInterface $spacecraft): TorpedoStorageInterface;

    public function getTorpedo(): TorpedoTypeInterface;

    public function setTorpedo(TorpedoTypeInterface $torpedoType): TorpedoStorageInterface;

    public function getStorage(): StorageInterface;

    public function setStorage(StorageInterface $storage): TorpedoStorageInterface;
}
