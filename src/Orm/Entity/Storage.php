<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

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

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $user_id;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $commodity_id = 0;

    //TODO rename to amount
    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $count = 0;

    /**
     * @Column(type="integer", nullable=true)
     *
     * @var int|null
     */
    private $colony_id;

    /**
     * @Column(type="integer", nullable=true)
     *
     * @var int|null
     */
    private $ship_id;

    /**
     * @Column(type="integer", nullable=true)
     *
     * @var int|null
     */
    private $torpedo_storage_id;

    /**
     * @Column(type="integer", nullable=true)
     *
     * @var int|null
     */
    private $tradepost_id;

    /**
     * @Column(type="integer", nullable=true)
     *
     * @var int|null
     */
    private $tradeoffer_id;

    /**
     * @var UserInterface
     *
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @var CommodityInterface
     *
     * @ManyToOne(targetEntity="Stu\Orm\Entity\Commodity")
     * @JoinColumn(name="commodity_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $commodity;

    /**
     * @var null|ColonyInterface
     *
     * @ManyToOne(targetEntity="Colony", inversedBy="storage")
     * @JoinColumn(name="colony_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $colony;

    /**
     * @var null|ShipInterface
     *
     * @ManyToOne(targetEntity="Ship", inversedBy="storage")
     * @JoinColumn(name="ship_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $ship;

    /**
     * @var null|TorpedoStorageInterface
     *
     * @OneToOne(targetEntity="TorpedoStorage", inversedBy="storage")
     * @JoinColumn(name="torpedo_storage_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $torpedoStorage;

    /**
     * @var null|TradePostInterface
     *
     * @ManyToOne(targetEntity="TradePost")
     * @JoinColumn(name="tradepost_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $tradePost;

    /**
     * @var null|TradeOfferInterface
     *
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
