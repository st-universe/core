<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Component\Ship\ShipModuleTypeEnum;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\DealsRepository")
 * @Table(
 *     name="stu_deals",
 *     indexes={
 *         @Index(name="deals_idx", columns={"id"})
 *     }
 * )
 **/
class Deals implements DealsInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="integer", nullable=true)  */
    private $faction_id;

    /** @Column(type="boolean") */
    private $auction = FALSE;

    /** @Column(type="integer", nullable=true) */
    private $amount = 0;

    /** @Column(type="integer", nullable=true) */
    private $give_commodity = NULL;

    /** @Column(type="integer", nullable=true) */
    private $want_commodity = NULL;

    /** @Column(type="integer", nullable=true) */
    private $give_commodity_amonut = NULL;

    /** @Column(type="integer", nullable=true) */
    private $want_commodity_amount = NULL;

    /** @Column(type="integer", nullable=true) */
    private $want_prestige = NULL;

    /** @Column(type="integer", nullable=true) */
    private $buildplan_id = NULL;

    /** @Column(type="boolean", nullable=true) */
    private $ship = NULL;

    /** @Column(type="integer") */
    private $time;

    /** @Column(type="integer") */
    private $end;

    /** @Column(type="integer", nullable=true) */
    private $auction_user;

    /** @Column(type="integer", nullable=true) */
    private $auction_amount;


    /**
     * @ManyToOne(targetEntity="Commodity")
     * @JoinColumn(name="want_commodity", referencedColumnName="id", onDelete="CASCADE")
     */
    private $wantedCommodity;

    /**
     * @ManyToOne(targetEntity="Commodity")
     * @JoinColumn(name="give_commodity", referencedColumnName="id", onDelete="CASCADE")
     */
    private $giveCommodity;

    /**
     * @ManyToOne(targetEntity="ShipBuildplan")
     * @JoinColumn(name="buildplan_id", referencedColumnName="id")
     */
    private $buildplan;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="auction_user", referencedColumnName="id")
     */
    private $auctionuser;

    /**
     * @ManyToOne(targetEntity="DealsAuction")
     * @JoinColumn(name="id", referencedColumnName="id")
     */
    private $auctionid;

    public function getId(): int
    {
        return $this->id;
    }

    public function setFaction(FactionInterface $faction): DealsInterface
    {
        $this->faction_id = $faction;

        return $this;
    }

    public function getFaction(): FactionInterface
    {
        return $this->faction_id;
    }

    public function setAuction(bool $auction): DealsInterface
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


    public function setAmount(int $dealamount): DealsInterface
    {
        $this->amount = $dealamount;

        return $this;
    }

    public function getgiveCommodityId(): ?int
    {
        return $this->give_commodity;
    }


    public function setgiveCommodityId(CommodityInterface $givecommodity): DealsInterface
    {
        $this->give_commodity = $givecommodity;

        return $this;
    }

    public function getwantCommodityId(): ?int
    {
        return $this->want_commodity;
    }

    public function setwantCommodityId(CommodityInterface $wantcommodity): DealsInterface
    {
        $this->want_commodity = $wantcommodity;

        return $this;
    }

    public function getgiveCommodityAmount(): int
    {
        return $this->give_commodity_amonut;
    }


    public function setgiveCommodityAmount(CommodityInterface $givecommodityamount): DealsInterface
    {
        $this->give_commodity_amonut = $givecommodityamount;

        return $this;
    }

    public function getwantCommodityAmount(): int
    {
        return $this->want_commodity_amount;
    }

    public function setwantCommodityAmount(CommodityInterface $wantcommodityamount): DealsInterface
    {
        $this->want_commodity_amount = $wantcommodityamount;

        return $this;
    }

    public function getwantPrestige(): ?int
    {
        return $this->want_prestige;
    }

    public function setwantPrestige(int $wantprestige): DealsInterface
    {
        $this->want_prestige = $wantprestige;

        return $this;
    }

    public function getBuildplanId(): ?int
    {
        return $this->buildplan_id;
    }

    public function setBuildplanId(int $buildplanid): DealsInterface
    {
        $this->buildplan_id = $buildplanid;

        return $this;
    }

    public function getShip(): bool
    {
        return $this->ship;
    }

    public function setShip(bool $ship): DealsInterface
    {
        $this->ship = $ship;

        return $this;
    }

    public function getTime(): int
    {
        return $this->time;
    }

    public function setTime(int $time): DealsInterface
    {
        $this->time = $time;

        return $this;
    }

    public function getEnd(): int
    {
        return $this->end;
    }

    public function setEnd(int $end): DealsInterface
    {
        $this->end = $end;

        return $this;
    }

    public function getAuctionUserId(): ?int
    {
        return $this->auction_user;
    }

    public function getAuctionAmount(): ?int
    {
        return $this->auction_amount;
    }

    public function setAuctionAmount(int $auction_amount): DealsInterface
    {
        $this->auction_amount = $auction_amount;

        return $this;
    }

    public function getAuctionUser(): ?UserInterface
    {
        return $this->auctionuser;
    }

    public function setAuctionUser(UserInterface $auctionuser): DealsInterface
    {
        $this->auctionuser = $auctionuser;

        return $this;
    }

    public function getWantedCommodity(): CommodityInterface
    {
        return $this->wantedCommodity;
    }

    public function setWantedCommodity(CommodityInterface $wantedCommodity): DealsInterface
    {
        $this->wantedCommodity = $wantedCommodity;

        return $this;
    }

    public function getGiveCommodity(): CommodityInterface
    {
        return $this->giveCommodity;
    }

    public function setGiveCommodity(CommodityInterface $giveCommodity): DealsInterface
    {
        $this->giveCommodity = $giveCommodity;

        return $this;
    }

    public function getModules(): array
    {
        $modules = [];

        foreach ($this->getBuildplan()->getModules() as $obj) {
            $module = $obj->getModule();
            $index = $module->getType() === ShipModuleTypeEnum::MODULE_TYPE_SPECIAL ? $module->getId() : $module->getType();
            $modules[$index] = $module;
        }
        return $modules;
    }

    public function getBuildplan(): ?ShipBuildplanInterface
    {
        return $this->buildplan;
    }

    public function getRumpId(): int
    {
        $rumpid = $this->getBuildplan()->getRumpId();
        return $rumpid;
    }

    public function getBuildplanName(): string
    {
        $bpname = $this->getBuildplan()->getName();
        return $bpname;
    }

    public function getAuctions(): ?DealsAuctionInterface
    {
        return $this->auctionid;
    }

    public function setAuctions(DealsAuctionInterface $auctionid): DealsInterface
    {
        $this->auctionid = $auctionid;

        return $this;
    }
}