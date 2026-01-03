<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\ORM\Mapping\Table;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Repository\TradePostRepository;

#[Table(name: 'stu_trade_posts')]
#[Index(name: 'trade_network_idx', columns: ['trade_network'])]
#[Index(name: 'trade_post_station_idx', columns: ['station_id'])]
#[Entity(repositoryClass: TradePostRepository::class)]
class TradePost
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'string')]
    private string $name = '';

    #[Column(type: 'text')]
    private string $description = '';

    #[Column(type: 'smallint')]
    private int $trade_network = 0;

    #[Column(type: 'smallint')]
    private int $level = 0;

    #[Column(type: 'integer')]
    private int $transfer_capacity = 0;

    #[Column(type: 'integer')]
    private int $storage = 0;

    #[Column(type: 'boolean', nullable: true)]
    private ?bool $is_dock_pm_auto_read = null;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id')]
    private User $user;

    /**
     * @var ArrayCollection<int, TradeLicenseInfo>
     */
    #[OneToMany(targetEntity: TradeLicenseInfo::class, mappedBy: 'tradePost')]
    #[OrderBy(['id' => 'DESC'])]
    private Collection $licenseInfos;

    #[OneToOne(targetEntity: Station::class, inversedBy: 'tradePost')]
    #[JoinColumn(name: 'station_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Station $station;

    /**
     * @var ArrayCollection<int, CrewAssignment>
     */
    #[OneToMany(targetEntity: CrewAssignment::class, mappedBy: 'tradepost')]
    private Collection $crewAssignments;

    public function __construct()
    {
        $this->licenseInfos = new ArrayCollection();
        $this->crewAssignments = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user->getId();
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): TradePost
    {
        $this->user = $user;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): TradePost
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): TradePost
    {
        $this->description = $description;

        return $this;
    }

    public function getTradeNetwork(): int
    {
        return $this->trade_network;
    }

    public function setTradeNetwork(int $tradeNetwork): TradePost
    {
        $this->trade_network = $tradeNetwork;

        return $this;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): TradePost
    {
        $this->level = $level;

        return $this;
    }

    public function getTransferCapacity(): int
    {
        return $this->transfer_capacity;
    }

    public function setTransferCapacity(int $transferCapacity): TradePost
    {
        $this->transfer_capacity = $transferCapacity;

        return $this;
    }

    public function getStorage(): int
    {
        return $this->storage;
    }

    public function setStorage(int $storage): TradePost
    {
        $this->storage = $storage;

        return $this;
    }

    public function isDockPmAutoRead(): bool
    {
        return $this->is_dock_pm_auto_read ?? false;
    }

    public function setIsDockPmAutoRead(bool $value): TradePost
    {
        $this->is_dock_pm_auto_read = $value;

        return $this;
    }

    public function getLatestLicenseInfo(): ?TradeLicenseInfo
    {
        if ($this->licenseInfos->isEmpty()) {
            return null;
        }
        return $this->licenseInfos->first();
    }

    public function getStation(): Station
    {
        return $this->station;
    }

    public function setStation(Station $station): TradePost
    {
        $this->station = $station;

        return $this;
    }

    /**
     * @return Collection<int, CrewAssignment>
     */
    public function getCrewAssignments(): Collection
    {
        return $this->crewAssignments;
    }

    public function getCrewCountOfUser(
        User $user
    ): int {
        $count = 0;

        foreach ($this->getCrewAssignments() as $crewAssignment) {
            if ($crewAssignment->getUser()->getId() === $user->getId()) {
                $count++;
            }
        }

        return $count;
    }

    public function isNpcTradepost(): bool
    {
        return $this->getUserId() < UserConstants::USER_FIRST_ID;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
