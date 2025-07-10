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
use LogicException;
use Stu\Orm\Attribute\TruncateOnGameReset;
use Stu\Orm\Repository\TorpedoStorageRepository;

#[Table(name: 'stu_torpedo_storage')]
#[Index(name: 'torpedo_storage_spacecraft_idx', columns: ['spacecraft_id'])]
#[Entity(repositoryClass: TorpedoStorageRepository::class)]
#[TruncateOnGameReset]
class TorpedoStorage
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $spacecraft_id;

    #[Column(type: 'integer', length: 3)]
    private int $torpedo_type;

    #[OneToOne(targetEntity: Spacecraft::class, inversedBy: 'torpedoStorage')]
    #[JoinColumn(name: 'spacecraft_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Spacecraft $spacecraft;

    #[ManyToOne(targetEntity: TorpedoType::class)]
    #[JoinColumn(name: 'torpedo_type', nullable: false, referencedColumnName: 'id')]
    private TorpedoType $torpedo;

    #[OneToOne(targetEntity: Storage::class, mappedBy: 'torpedoStorage')]
    private ?Storage $storage;

    public function getId(): int
    {
        return $this->id;
    }

    public function getSpacecraft(): Spacecraft
    {
        return $this->spacecraft;
    }

    public function setSpacecraft(Spacecraft $spacecraft): TorpedoStorage
    {
        $this->spacecraft = $spacecraft;
        return $this;
    }

    public function getTorpedo(): TorpedoType
    {
        return $this->torpedo;
    }

    public function setTorpedo(TorpedoType $torpedoType): TorpedoStorage
    {
        $this->torpedo = $torpedoType;
        return $this;
    }

    public function getStorage(): Storage
    {
        return $this->storage ?? throw new LogicException('TorpedoStorage has no storage');
    }

    public function setStorage(Storage $storage): TorpedoStorage
    {
        $this->storage = $storage;

        return $this;
    }
}
