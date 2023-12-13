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

#[Table(name: 'stu_trade_license_info')]
#[Index(name: 'trade_license_info_post_idx', columns: ['posts_id'])]
#[Entity(repositoryClass: 'Stu\Orm\Repository\TradeLicenseInfoRepository')]
class TradeLicenseInfo implements TradeLicenseInfoInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $posts_id = 0;

    #[Column(type: 'integer')]
    private int $commodity_id = 0;

    #[Column(type: 'integer')]
    private int $amount = 0;

    #[Column(type: 'integer')]
    private int $days = 0;

    #[Column(type: 'integer')]
    private int $date = 0;

    #[ManyToOne(targetEntity: 'TradePost')]
    #[JoinColumn(name: 'posts_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private TradePostInterface $tradePost;

    #[ManyToOne(targetEntity: 'Commodity')]
    #[JoinColumn(name: 'commodity_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private CommodityInterface $commodity;

    public function getId(): int
    {
        return $this->id;
    }

    public function getTradepost(): TradePostInterface
    {
        return $this->tradePost;
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
