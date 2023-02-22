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
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\TradeLicenseInfoRepository")
 * @Table(
 *     name="stu_trade_license_info",
 *     indexes={
 *         @Index(name="trade_license_info_post_idx", columns={"posts_id"})
 *     }
 * )
 **/
class TradeLicenseInfo implements TradeLicenseInfoInterface
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
    private $posts_id = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $commodity_id = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $amount = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $days = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $date = 0;

    /**
     * @var TradePostInterface
     *
     * @ManyToOne(targetEntity="TradePost")
     * @JoinColumn(name="posts_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $tradePost;

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

    public function setTradepost(TradePostInterface $tradepost): TradeLicenseInfoInterface
    {
        $this->tradePost = $tradepost;

        return $this;
    }

    public function getTradePostId(): int
    {
        return $this->posts_id;
    }

    public function setTradePostId(int $posts_id): TradeLicenseInfoInterface
    {
        $this->posts_id = $posts_id;

        return $this;
    }

    public function getCommodityId(): int
    {
        return $this->commodity_id;
    }

    public function getCommodity(): CommodityInterface
    {
        return $this->commodity;
    }

    public function setCommodity(CommodityInterface $commodity): TradeLicenseInfoInterface
    {
        $this->commodity = $commodity;

        return $this;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): TradeLicenseInfoInterface
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): TradeLicenseInfoInterface
    {
        $this->date = $date;

        return $this;
    }

    public function getDays(): int
    {
        return $this->days;
    }

    public function setDays(int $days): TradeLicenseInfoInterface
    {
        $this->days = $days;

        return $this;
    }
}
