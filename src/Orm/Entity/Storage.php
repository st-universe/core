<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\StorageRepository")
 * @Table(
 *     name="stu_storage",
 *     indexes={
 *         @Index(name="storage_user_idx", columns={"user_id"}),
 *         @Index(name="storage_commodity_idx", columns={"commodity_id"}),
 *         @Index(name="storage_colony_idx", columns={"colony_id"}),
 *         @Index(name="storage_ship_idx", columns={"ship_id"}),
 *         @Index(name="storage_torpedo_idx", columns={"torpedo_storage_id"}),
 *         @Index(name="storage_tradepost_idx", columns={"tradepost_id"}),
 *         @Index(name="storage_tradeoffer_idx", columns={"tradeoffer_id"})
 *     }
 * )
 **/
class Storage implements StorageInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private int $id;

    /**
     * @Column(type="integer")
     *
     */
    private int $user_id;

    /**
     * @Column(type="integer")
     *
     */
    private int $commodity_id = 0;

    //TODO rename to amount
    /**
     * @Column(type="integer")
     *
     */
    private int $count = 0;

    /**
     * @Column(type="integer", nullable=true)
     *
     */
    private ?int $colony_id;

    /**
     * @Column(type="integer", nullable=true)
     *
     */
    private ?int $ship_id;

    /**
     * @Column(type="integer", nullable=true)
     *
     */
    private ?int $torpedo_storage_id;

    /**
     * @Column(type="integer", nullable=true)
     *
     */
    private ?int $tradepost_id;

    /**
     * @Column(type="integer", nullable=true)
     *
     */
    private ?int $tradeoffer_id;

    /**
     *
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?UserInterface $user = null;

    /**
     *
     * @ManyToOne(targetEntity="Stu\Orm\Entity\Commodity")
     * @JoinColumn(name="commodity_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private CommodityInterface $commodity;

    /**
     *
     * @ManyToOne(targetEntity="Colony", inversedBy="storage")
     * @JoinColumn(name="colony_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?ColonyInterface $colony = null;

    /**
     *
     * @ManyToOne(targetEntity="Ship", inversedBy="storage")
     * @JoinColumn(name="ship_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?ShipInterface $ship = null;

    /**
     *
     * @OneToOne(targetEntity="TorpedoStorage", inversedBy="storage")
     * @JoinColumn(name="torpedo_storage_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?TorpedoStorageInterface $torpedoStorage = null;

    /**
     *
     * @ManyToOne(targetEntity="TradePost")
     * @JoinColumn(name="tradepost_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?TradePostInterface $tradePost = null;

    /**
     *
     * @OneToOne(targetEntity="TradeOffer", inversedBy="storage")
     * @JoinColumn(name="tradeoffer_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?TradeOfferInterface $tradeOffer = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUser(UserInterface $user): StorageInterface
    {
        $this->user = $user;

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

    public function getTorpedoStorage(): ?TorpedoStorageInterface
    {
        return $this->torpedoStorage;
    }

    public function setTorpedoStorage(TorpedoStorageInterface $torpedoStorage): StorageInterface
    {
        $this->torpedoStorage = $torpedoStorage;
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
