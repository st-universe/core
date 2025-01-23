<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Override;
use Stu\Orm\Repository\TorpedoStorageRepository;

#[Table(name: 'stu_torpedo_storage')]
#[Index(name: 'torpedo_storage_spacecraft_idx', columns: ['spacecraft_id'])]
#[Entity(repositoryClass: TorpedoStorageRepository::class)]
class TorpedoStorage implements TorpedoStorageInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $spacecraft_id;

    #[Column(type: 'integer', length: 3)]
    private int $torpedo_type;

    #[OneToOne(targetEntity: 'Spacecraft', inversedBy: 'torpedoStorage')]
    #[JoinColumn(name: 'spacecraft_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private SpacecraftInterface $spacecraft;

    #[ManyToOne(targetEntity: 'TorpedoType')]
    #[JoinColumn(name: 'torpedo_type', referencedColumnName: 'id')]
    private TorpedoTypeInterface $torpedo;

    #[OneToOne(targetEntity: 'Storage', mappedBy: 'torpedoStorage')]
    private StorageInterface $storage;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getSpacecraft(): SpacecraftInterface
    {
        return $this->spacecraft;
    }

    #[Override]
    public function setSpacecraft(SpacecraftInterface $spacecraft): TorpedoStorageInterface
    {
        $this->spacecraft = $spacecraft;
        return $this;
    }

    #[Override]
    public function getTorpedo(): TorpedoTypeInterface
    {
        return $this->torpedo;
    }

    #[Override]
    public function setTorpedo(TorpedoTypeInterface $torpedoType): TorpedoStorageInterface
    {
        $this->torpedo = $torpedoType;
        return $this;
    }

    #[Override]
    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    #[Override]
    public function setStorage(StorageInterface $storage): TorpedoStorageInterface
    {
        $this->storage = $storage;

        return $this;
    }
}
