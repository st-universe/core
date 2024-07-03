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
use Override;
use Stu\Orm\Repository\TradeLicenseInfoRepository;

#[Table(name: 'stu_trade_license_info')]
#[Index(name: 'trade_license_info_post_idx', columns: ['posts_id'])]
#[Entity(repositoryClass: TradeLicenseInfoRepository::class)]
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

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getTradepost(): TradePostInterface
    {
        return $this->tradePost;
    }

    #[Override]
    public function setTradepost(TradePostInterface $tradepost): TradeLicenseInfoInterface
    {
        $this->tradePost = $tradepost;

        return $this;
    }

    #[Override]
    public function getTradePostId(): int
    {
        return $this->posts_id;
    }

    #[Override]
    public function setTradePostId(int $posts_id): TradeLicenseInfoInterface
    {
        $this->posts_id = $posts_id;

        return $this;
    }

    #[Override]
    public function getCommodityId(): int
    {
        return $this->commodity_id;
    }

    #[Override]
    public function getCommodity(): CommodityInterface
    {
        return $this->commodity;
    }

    #[Override]
    public function setCommodity(CommodityInterface $commodity): TradeLicenseInfoInterface
    {
        $this->commodity = $commodity;

        return $this;
    }

    #[Override]
    public function getAmount(): int
    {
        return $this->amount;
    }

    #[Override]
    public function setAmount(int $amount): TradeLicenseInfoInterface
    {
        $this->amount = $amount;

        return $this;
    }

    #[Override]
    public function getDate(): int
    {
        return $this->date;
    }

    #[Override]
    public function setDate(int $date): TradeLicenseInfoInterface
    {
        $this->date = $date;

        return $this;
    }

    #[Override]
    public function getDays(): int
    {
        return $this->days;
    }

    #[Override]
    public function setDays(int $days): TradeLicenseInfoInterface
    {
        $this->days = $days;

        return $this;
    }
}
