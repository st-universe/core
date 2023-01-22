<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\BasicTradeRepository")
 * @Table(
 *     name="stu_basic_trade",
 *     indexes={
 *         @Index(name="base_trade_idx", columns={"faction_id","commodity_id","date_ms"})
 *     }
 * )
 **/
class BasicTrade implements BasicTradeInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="integer", nullable=true)  */
    private $faction_id;

    /** @Column(type="integer") */
    private $commodity_id = 0;

    /** @Column(type="smallint") */
    private $buy_sell = 0;

    /** @Column(type="integer") */
    private $value = 0;

    /** @Column(type="bigint", nullable=true) */
    private $date_ms;

    /** @Column(type="string", nullable=true) */
    private $uniqid;

    /** @Column(type="integer", nullable=true) */
    private $user_id;

    /**
     * @var FactionInterface
     *
     * @ManyToOne(targetEntity="Faction")
     * @JoinColumn(name="faction_id", referencedColumnName="id")
     */
    private $faction;

    /**
     * @var CommodityInterface
     *
     * @ManyToOne(targetEntity="Commodity")
     * @JoinColumn(name="commodity_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $commodity;

    public function getId(): int
    {
        return $this->id;
    }

    public function setFaction(FactionInterface $faction): BasicTradeInterface
    {
        $this->faction = $faction;

        return $this;
    }

    public function getFaction(): FactionInterface
    {
        return $this->faction;
    }

    public function setCommodity(CommodityInterface $commodity): BasicTradeInterface
    {
        $this->commodity = $commodity;

        return $this;
    }

    public function getCommodity(): CommodityInterface
    {
        return $this->commodity;
    }

    public function setBuySell(int $buySell): BasicTradeInterface
    {
        $this->buy_sell = $buySell;

        return $this;
    }

    public function getBuySell(): int
    {
        return $this->buy_sell;
    }

    public function setValue(int $value): BasicTradeInterface
    {
        $this->value = $value;

        return $this;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function setDate(int $date): BasicTradeInterface
    {
        $this->date_ms = $date;

        return $this;
    }

    public function getDate(): int
    {

        return (int)$this->date_ms;
    }

    public function setUniqId(string $uniqid): BasicTradeInterface
    {
        $this->uniqid = $uniqid;

        return $this;
    }

    public function getUniqId(): string
    {
        return $this->uniqid;
    }

    public function setUserId(int $userId): BasicTradeInterface
    {
        $this->user_id = $userId;

        return $this;
    }
}
