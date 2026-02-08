<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\ORM\Mapping\Table;
use RuntimeException;
use Stu\Lib\Interaction\EntityWithInteractionCheckInterface;
use Stu\Lib\Map\EntityWithLocationInterface;
use Stu\Lib\Transfer\CommodityTransfer;
use Stu\Lib\Transfer\EntityWithStorageInterface;
use Stu\Lib\Transfer\TransferEntityTypeEnum;
use Stu\Orm\Repository\TrumfieldRepository;

#[Table(name: 'stu_trumfield')]
#[Entity(repositoryClass: TrumfieldRepository::class)]
class Trumfield implements
    EntityWithStorageInterface,
    EntityWithLocationInterface,
    EntityWithInteractionCheckInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer', length: 6)]
    private int $huelle = 0;

    #[Column(type: 'integer')]
    private int $former_rump_id = 0;

    /**
     * @var ArrayCollection<int, Storage>
     */
    #[OneToMany(targetEntity: Storage::class, mappedBy: 'trumfield', indexBy: 'commodity_id')]
    #[OrderBy(['commodity_id' => 'ASC'])]
    private Collection $storage;

    #[ManyToOne(targetEntity: Location::class, inversedBy: 'trumfields')]
    #[JoinColumn(name: 'location_id', nullable: false, referencedColumnName: 'id')]
    private Location $location;

    #[\Override]
    public function getId(): int
    {
        return $this->id;
    }

    public function getHull(): int
    {
        return $this->huelle;
    }

    public function setHull(int $hull): Trumfield
    {
        $this->huelle = $hull;
        return $this;
    }

    public function getFormerRumpId(): int
    {
        return $this->former_rump_id;
    }

    public function setFormerRumpId(int $formerRumpId): Trumfield
    {
        $this->former_rump_id = $formerRumpId;
        return $this;
    }

    #[\Override]
    public function getStorage(): Collection
    {
        return $this->storage;
    }

    #[\Override]
    public function getStorageSum(): int
    {
        return array_reduce(
            $this->getStorage()->getValues(),
            fn (int $sum, Storage $storage): int => $sum + $storage->getAmount(),
            0
        );
    }

    #[\Override]
    public function getMaxStorage(): int
    {
        return $this->getStorageSum();
    }

    #[\Override]
    public function getBeamableStorage(): Collection
    {
        return CommodityTransfer::excludeNonBeamable($this->storage);
    }

    #[\Override]
    public function getCrewAssignments(): Collection
    {
        return new ArrayCollection();
    }

    #[\Override]
    public function getLocation(): Map|StarSystemMap
    {
        if ($this->location instanceof Map || $this->location instanceof StarSystemMap) {
            return $this->location;
        }

        throw new RuntimeException('unknown type');
    }

    public function setLocation(Location $location): Trumfield
    {
        $this->location = $location;

        return $this;
    }

    #[\Override]
    public function getUser(): ?User
    {
        return null;
    }

    #[\Override]
    public function getTransferEntityType(): TransferEntityTypeEnum
    {
        return TransferEntityTypeEnum::TRUMFIELD;
    }

    #[\Override]
    public function getName(): string
    {
        return $this->getTransferEntityType()->getName();
    }

    #[\Override]
    public function getHref(): string
    {
        return '';
    }
}
