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
use Override;
use RuntimeException;
use Stu\Lib\Transfer\CommodityTransfer;
use Stu\Lib\Transfer\TransferEntityTypeEnum;
use Stu\Orm\Repository\TrumfieldRepository;

#[Table(name: 'stu_trumfield')]
#[Entity(repositoryClass: TrumfieldRepository::class)]
class Trumfield implements TrumfieldInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer', length: 6)]
    private int $huelle = 0;

    #[Column(type: 'integer')]
    private int $former_rump_id = 0;

    #[Column(type: 'integer')]
    private int $location_id = 0;

    /**
     * @var ArrayCollection<int, StorageInterface>
     */
    #[OneToMany(targetEntity: 'Storage', mappedBy: 'trumfield', indexBy: 'commodity_id')]
    #[OrderBy(['commodity_id' => 'ASC'])]
    private Collection $storage;

    #[ManyToOne(targetEntity: 'Location')]
    #[JoinColumn(name: 'location_id', referencedColumnName: 'id')]
    private LocationInterface $location;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getHull(): int
    {
        return $this->huelle;
    }

    #[Override]
    public function setHuell(int $hull): TrumfieldInterface
    {
        $this->huelle = $hull;
        return $this;
    }

    #[Override]
    public function getFormerRumpId(): int
    {
        return $this->former_rump_id;
    }

    #[Override]
    public function setFormerRumpId(int $formerRumpId): TrumfieldInterface
    {
        $this->former_rump_id = $formerRumpId;
        return $this;
    }

    #[Override]
    public function getStorage(): Collection
    {
        return $this->storage;
    }

    #[Override]
    public function getStorageSum(): int
    {
        return array_reduce(
            $this->getStorage()->getValues(),
            fn(int $sum, StorageInterface $storage): int => $sum + $storage->getAmount(),
            0
        );
    }

    #[Override]
    public function getMaxStorage(): int
    {
        return $this->getStorageSum();
    }

    #[Override]
    public function getBeamableStorage(): Collection
    {
        return CommodityTransfer::excludeNonBeamable($this->storage);
    }

    #[Override]
    public function getCrewAssignments(): Collection
    {
        return new ArrayCollection();
    }

    #[Override]
    public function getLocation(): MapInterface|StarSystemMapInterface
    {
        if (
            $this->location instanceof MapInterface
            || $this->location instanceof StarSystemMapInterface
        ) {
            return $this->location;
        }

        throw new RuntimeException('unknown type');
    }

    #[Override]
    public function setLocation(LocationInterface $location): TrumfieldInterface
    {
        $this->location = $location;

        return $this;
    }

    #[Override]
    public function getUser(): ?UserInterface
    {
        return null;
    }

    #[Override]
    public function getTransferEntityType(): TransferEntityTypeEnum
    {
        return TransferEntityTypeEnum::TRUMFIELD;
    }

    #[Override]
    public function getHref(): string
    {
        return '';
    }
}
