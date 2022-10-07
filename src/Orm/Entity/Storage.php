<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\StorageRepository")
 * @Table(
 *     name="stu_storage"
 * )
 **/
class Storage implements StorageInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="integer") */
    private $user_id;

    /** @Column(type="integer") */
    private $commodity_id = 0;

    //TODO rename to amount
    /** @Column(type="integer") */
    private $count = 0;

    /** @Column(type="integer", nullable=true) * */
    private $colony_id;

    /** @Column(type="integer", nullable=true) * */
    private $ship_id;

    /** @Column(type="integer", nullable=true) * */
    private $tradepost_id;

    /** @Column(type="integer", nullable=true) * */
    private $tradeoffer_id;

    /**
     * @ManyToOne(targetEntity="Stu\Orm\Entity\Commodity")
     * @JoinColumn(name="commodity_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $commodity;

    /**
     * @ManyToOne(targetEntity="Colony", inversedBy="storageNew")
     * @JoinColumn(name="colony_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $colony;

    /**
     * @ManyToOne(targetEntity="Ship", inversedBy="storageNew")
     * @JoinColumn(name="ship_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $ship;

    /**
     * @ManyToOne(targetEntity="TradePost")
     * @JoinColumn(name="tradepost_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $tradePost;

    /**
     * @OneToOne(targetEntity="TradeOffer", inversedBy="storage")
     * @JoinColumn(name="tradeoffer_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $tradeOffer;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $userId): StorageInterface
    {
        $this->user_id = $userId;

        return $this;
    }

    public function getCommodityId(): int
    {
        return $this->commodity_id;
    }

    public function getAmount(): int
    {
        return $this->count;
    }

    public function setAmount(int $amount): StorageInterface
    {
        $this->count = $amount;

        return $this;
    }

    public function getCommodity(): CommodityInterface
    {
        return $this->commodity;
    }

    public function setCommodity(CommodityInterface $commodity): StorageInterface
    {
        $this->commodity = $commodity;

        return $this;
    }

    public function getColony(): ?ColonyInterface
    {
        return $this->colony;
    }

    public function setColony(ColonyInterface $colony): StorageInterface
    {
        $this->colony = $colony;
        return $this;
    }

    public function getShip(): ?ShipInterface
    {
        return $this->ship;
    }

    public function setShip(ShipInterface $ship): StorageInterface
    {
        $this->ship = $ship;
        return $this;
    }

    public function getTradePost(): ?TradePostInterface
    {
        return $this->tradePost;
    }

    public function setTradePost(TradePostInterface $tradePost): StorageInterface
    {
        $this->tradePost = $tradePost;
        return $this;
    }

    public function getTradeOffer(): ?TradeOfferInterface
    {
        return $this->tradeOffer;
    }

    public function setTradeOffer(TradeOfferInterface $tradeOffer): StorageInterface
    {
        $this->tradeOffer = $tradeOffer;
        return $this;
    }
}
