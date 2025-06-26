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
use Stu\Orm\Repository\DealsRepository;

#[Table(name: 'stu_deals')]
#[Entity(repositoryClass: DealsRepository::class)]
class Deals
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'boolean')]
    private bool $auction = false;

    #[Column(type: 'integer', nullable: true)]
    private ?int $amount = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $give_commodity = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $want_commodity = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $give_commodity_amonut = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $want_commodity_amount = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $want_prestige = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $buildplan_id = null;

    #[Column(type: 'boolean', nullable: true)]
    private ?bool $ship = null;

    #[Column(type: 'integer')]
    private int $start;

    #[Column(type: 'integer')]
    private int $end;

    #[Column(type: 'integer', nullable: true)]
    private ?int $taken_time = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $auction_user = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $auction_amount = null;

    #[ManyToOne(targetEntity: Commodity::class)]
    #[JoinColumn(name: 'want_commodity', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Commodity $wantedCommodity;

    #[ManyToOne(targetEntity: Commodity::class)]
    #[JoinColumn(name: 'give_commodity', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Commodity $giveCommodity;

    #[ManyToOne(targetEntity: SpacecraftBuildplan::class)]
    #[JoinColumn(name: 'buildplan_id', referencedColumnName: 'id')]
    private ?SpacecraftBuildplan $buildplan = null;

    #[ManyToOne(targetEntity: Faction::class)]
    #[JoinColumn(name: 'faction_id', referencedColumnName: 'id')]
    private ?Faction $faction;

    /**
     * @var ArrayCollection<int, AuctionBid>
     */
    #[OneToMany(targetEntity: AuctionBid::class, mappedBy: 'auction')]
    #[OrderBy(['max_amount' => 'ASC'])]
    private Collection $auctionBids;

    public function __construct()
    {
        $this->auctionBids = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setAuction(bool $auction): Deals
    {
        $this->auction = $auction;

        return $this;
    }

    public function getAuction(): bool
    {
        return $this->auction;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }


    public function setAmount(int $amount): Deals
    {
        $this->amount = $amount;

        return $this;
    }

    public function getgiveCommodityId(): ?int
    {
        return $this->give_commodity;
    }

    public function getwantCommodityId(): ?int
    {
        return $this->want_commodity;
    }

    public function getgiveCommodityAmount(): ?int
    {
        return $this->give_commodity_amonut;
    }


    public function getwantCommodityAmount(): ?int
    {
        return $this->want_commodity_amount;
    }

    public function getWantPrestige(): ?int
    {
        return $this->want_prestige;
    }

    public function setwantPrestige(int $wantprestige): Deals
    {
        $this->want_prestige = $wantprestige;

        return $this;
    }

    public function getBuildplanId(): ?int
    {
        return $this->buildplan_id;
    }

    public function setBuildplanId(int $buildplanid): Deals
    {
        $this->buildplan_id = $buildplanid;

        return $this;
    }

    public function getShip(): ?bool
    {
        return $this->ship;
    }

    public function setShip(bool $ship): Deals
    {
        $this->ship = $ship;

        return $this;
    }

    public function getStart(): int
    {
        return $this->start;
    }

    public function getEnd(): int
    {
        return $this->end;
    }

    public function setEnd(int $end): Deals
    {
        $this->end = $end;

        return $this;
    }

    public function getTakenTime(): ?int
    {
        return $this->taken_time;
    }

    public function setTakenTime(int $time): Deals
    {
        $this->taken_time = $time;

        return $this;
    }

    public function getAuctionAmount(): ?int
    {
        return $this->auction_amount;
    }

    public function setAuctionAmount(int $auction_amount): Deals
    {
        $this->auction_amount = $auction_amount;

        return $this;
    }

    public function getAuctionUser(): ?User
    {
        $highestBid = $this->getHighestBid();

        return $highestBid !== null ? $highestBid->getUser() : null;
    }

    public function setAuctionUser(int $auction_user): Deals
    {
        $this->auction_user = $auction_user;

        return $this;
    }

    public function getWantedCommodity(): Commodity
    {
        return $this->wantedCommodity;
    }

    public function setWantedCommodity(Commodity $wantedCommodity): Deals
    {
        $this->wantedCommodity = $wantedCommodity;

        return $this;
    }

    public function getGiveCommodity(): Commodity
    {
        return $this->giveCommodity;
    }

    public function setGiveCommodity(Commodity $giveCommodity): Deals
    {
        $this->giveCommodity = $giveCommodity;

        return $this;
    }

    /**
     * @return array<int, Module>
     */
    public function getModules(): array
    {
        return $this->getBuildplan()
            ->getModules()
            ->map(fn(BuildplanModule $buildplanModule): Module => $buildplanModule->getModule())
            ->toArray();
    }

    public function getBuildplan(): ?SpacecraftBuildplan
    {
        return $this->buildplan;
    }

    public function getRumpId(): int
    {
        return $this->getBuildplan()->getRumpId();
    }

    public function getCrew(): int
    {
        return $this->getBuildplan() == null ? 0 : $this->getBuildplan()->getCrew();
    }

    public function getBuildplanName(): string
    {
        return $this->getBuildplan()->getName();
    }

    /**
     * @return Collection<int, AuctionBid>
     */
    public function getAuctionBids(): Collection
    {
        return $this->auctionBids;
    }

    public function getHighestBid(): ?AuctionBid
    {
        return $this->getAuctionBids()->count() > 0 ? $this->getAuctionBids()->last() : null;
    }

    public function isPrestigeCost(): bool
    {
        return $this->getWantPrestige() !== null;
    }
}
