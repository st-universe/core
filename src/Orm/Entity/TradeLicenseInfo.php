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
use Stu\Orm\Repository\TradeLicenseInfoRepository;

#[Table(name: 'stu_trade_license_info')]
#[Index(name: 'trade_license_info_post_idx', columns: ['posts_id'])]
#[Entity(repositoryClass: TradeLicenseInfoRepository::class)]
class TradeLicenseInfo
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

    #[ManyToOne(targetEntity: TradePost::class)]
    #[JoinColumn(name: 'posts_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private TradePost $tradePost;

    #[ManyToOne(targetEntity: Commodity::class)]
    #[JoinColumn(name: 'commodity_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Commodity $commodity;

    public function getId(): int
    {
        return $this->id;
    }

    public function getTradepost(): TradePost
    {
        return $this->tradePost;
    }

    public function setTradepost(TradePost $tradepost): TradeLicenseInfo
    {
        $this->tradePost = $tradepost;

        return $this;
    }

    public function getTradePostId(): int
    {
        return $this->posts_id;
    }

    public function setTradePostId(int $posts_id): TradeLicenseInfo
    {
        $this->posts_id = $posts_id;

        return $this;
    }

    public function getCommodityId(): int
    {
        return $this->commodity_id;
    }

    public function getCommodity(): Commodity
    {
        return $this->commodity;
    }

    public function setCommodity(Commodity $commodity): TradeLicenseInfo
    {
        $this->commodity = $commodity;

        return $this;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): TradeLicenseInfo
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): TradeLicenseInfo
    {
        $this->date = $date;

        return $this;
    }

    public function getDays(): int
    {
        return $this->days;
    }

    public function setDays(int $days): TradeLicenseInfo
    {
        $this->days = $days;

        return $this;
    }
}
