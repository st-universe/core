<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

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

    /** @Column(type="integer") */
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

    /** @Column(type="bigint") */
    private $time;

    /** @Column(type="bigint") */
    private $end;


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

    public function getAmount(): int
    {
        return $this->amount;
    }


    public function setAmount(DealsInterface $amount): DealsInterface
    {
        $this->amount = $amount;

        return $this;
    }

    public function getgiveCommodity(): CommodityInterface
    {
        return $this->give_commodity;
    }


    public function setgiveCommodity(CommodityInterface $givecommodity): DealsInterface
    {
        $this->give_commodity = $givecommodity;

        return $this;
    }

    public function getwantCommodity(): CommodityInterface
    {
        return $this->want_commodity;
    }

    public function setwantCommodity(CommodityInterface $wantcommodity): DealsInterface
    {
        $this->want_commodity = $wantcommodity;

        return $this;
    }

    public function getgiveCommodityAmount(): CommodityInterface
    {
        return $this->give_commodity_amonut;
    }


    public function setgiveCommodityAmount(CommodityInterface $givecommodityamount): DealsInterface
    {
        $this->give_commodity_amonut = $givecommodityamount;

        return $this;
    }

    public function getwantCommodityAmount(): CommodityInterface
    {
        return $this->want_commodity_amount;
    }

    public function setwantCommodityAmount(CommodityInterface $wantcommodityamount): DealsInterface
    {
        $this->want_commodity_amount = $wantcommodityamount;

        return $this;
    }

    public function getwantPrestige(): int
    {
        return $this->want_prestige;
    }

    public function setwantPrestige(int $wantprestige): DealsInterface
    {
        $this->want_prestige = $wantprestige;

        return $this;
    }

    public function getBuildplanId(): int
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
}