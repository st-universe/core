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
use Stu\Module\PlayerSetting\Lib\UserEnum;

#[Table(name: 'stu_trade_posts')]
#[Index(name: 'trade_network_idx', columns: ['trade_network'])]
#[Index(name: 'trade_post_ship_idx', columns: ['ship_id'])]
#[Entity(repositoryClass: 'Stu\Orm\Repository\TradePostRepository')]
class TradePost implements TradePostInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'string')]
    private string $name = '';

    #[Column(type: 'text')]
    private string $description = '';

    #[Column(type: 'integer')]
    private int $ship_id = 0;

    #[Column(type: 'smallint')]
    private int $trade_network = 0;

    #[Column(type: 'smallint')]
    private int $level = 0;

    #[Column(type: 'integer')]
    private int $transfer_capacity = 0;

    #[Column(type: 'integer')]
    private int $storage = 0;

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private UserInterface $user;

    /**
     * @var ArrayCollection<int, TradeLicenseInfoInterface>
     */
    #[OneToMany(targetEntity: 'TradeLicenseInfo', mappedBy: 'tradePost')]
    #[OrderBy(['id' => 'DESC'])]
    private Collection $licenseInfos;

    #[OneToOne(targetEntity: 'Ship', inversedBy: 'tradePost')]
    #[JoinColumn(name: 'ship_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ShipInterface $ship;

    /**
     * @var Collection<int, ShipCrewInterface>
     */
    #[OneToMany(targetEntity: 'ShipCrew', mappedBy: 'tradepost')]
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
        return $this->user_id;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): TradePostInterface
    {
        $this->user = $user;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): TradePostInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): TradePostInterface
    {
        $this->description = $description;

        return $this;
    }

    public function getShipId(): int
    {
        return $this->ship_id;
    }

    public function setShipId(int $shipId): TradePostInterface
    {
        $this->ship_id = $shipId;

        return $this;
    }

    public function getTradeNetwork(): int
    {
        return $this->trade_network;
    }

    public function setTradeNetwork(int $tradeNetwork): TradePostInterface
    {
        $this->trade_network = $tradeNetwork;

        return $this;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): TradePostInterface
    {
        $this->level = $level;

        return $this;
    }

    public function getTransferCapacity(): int
    {
        return $this->transfer_capacity;
    }

    public function setTransferCapacity(int $transferCapacity): TradePostInterface
    {
        $this->transfer_capacity = $transferCapacity;

        return $this;
    }

    public function getStorage(): int
    {
        return $this->storage;
    }

    public function setStorage(int $storage): TradePostInterface
    {
        $this->storage = $storage;

        return $this;
    }

    public function getLatestLicenseInfo(): ?TradeLicenseInfoInterface
    {
        if ($this->licenseInfos->isEmpty()) {
            return null;
        }
        return $this->licenseInfos->first();
    }

    public function getShip(): ShipInterface
    {
        return $this->ship;
    }

    public function setShip(ShipInterface $ship): TradePostInterface
    {
        $this->ship = $ship;

        return $this;
    }

    public function getCrewAssignments(): Collection
    {
        return $this->crewAssignments;
    }

    public function getCrewCountOfUser(
        UserInterface $user
    ): int {
        $count = 0;

        foreach ($this->getCrewAssignments() as $crewAssignment) {
            if ($crewAssignment->getUser() === $user) {
                $count++;
            }
        }

        return $count;
    }

    public function isNpcTradepost(): bool
    {
        return $this->getUserId() < UserEnum::USER_FIRST_ID;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
