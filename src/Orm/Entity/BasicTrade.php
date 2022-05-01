<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;
//TODO indizes
/**
 * @Entity(repositoryClass="Stu\Orm\Repository\BasicTradeRepository")
 * @Table(
 *     name="stu_basic_trade"
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

    /** @Column(type="integer", nullable=true) */
    private $faction_id;

    /** @Column(type="integer") * */
    private $commodity_id = 0;

    /** @Column(type="smallint") */
    private $buy_sell = 0;

    /** @Column(type="integer") * */
    private $value = 0;

    /** @Column(type="integer") * */
    private $date = 0;

    /** @Column(type="string", nullable=true) */
    private $uniqid;

    /** @Column(type="integer", nullable=true) * */
    private $user_id;

    /**
     * @ManyToOne(targetEntity="Faction")
     * @JoinColumn(name="faction_id", referencedColumnName="id")
     */
    private $faction;

    /**
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
        $this->date = $date;

        return $this;
    }
}
