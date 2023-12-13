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

#[Table(name: 'stu_trade_offers')]
#[Index(name: 'trade_post_user_idx', columns: ['posts_id', 'user_id'])]
#[Entity(repositoryClass: 'Stu\Orm\Repository\TradeOfferRepository')]
class TradeOffer implements TradeOfferInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'integer')]
    private int $posts_id = 0;

    #[Column(type: 'smallint')]
    private int $amount = 0;

    #[Column(type: 'integer')]
    private int $wg_id = 0;

    #[Column(type: 'integer')]
    private int $wg_count = 0;

    #[Column(type: 'integer')]
    private int $gg_id = 0;

    #[Column(type: 'integer')]
    private int $gg_count = 0;

    #[Column(type: 'integer')]
    private int $date = 0;

    #[ManyToOne(targetEntity: 'TradePost')]
    #[JoinColumn(name: 'posts_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private TradePostInterface $tradePost;

    #[ManyToOne(targetEntity: 'Commodity')]
    #[JoinColumn(name: 'wg_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private CommodityInterface $wantedCommodity;

    #[ManyToOne(targetEntity: 'Commodity')]
    #[JoinColumn(name: 'gg_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private CommodityInterface $offeredCommodity;

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

    #[OneToOne(targetEntity: 'Storage', mappedBy: 'tradeOffer')]
    private StorageInterface $storage;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getTradePostId(): int
    {
        return $this->posts_id;
    }

    public function setTradePostId(int $tradePostId): TradeOfferInterface
    {
        $this->posts_id = $tradePostId;

        return $this;
    }

    public function getOfferCount(): int
    {
        return $this->amount;
    }

    public function setOfferCount(int $offerCount): TradeOfferInterface
    {
        $this->amount = $offerCount;

        return $this;
    }

    public function getWantedCommodityId(): int
    {
        return $this->wg_id;
    }

    public function setWantedCommodityId(int $wantedCommodityId): TradeOfferInterface
    {
        $this->wg_id = $wantedCommodityId;

        return $this;
    }

    public function getWantedCommodityCount(): int
    {
        return $this->wg_count;
    }

    public function setWantedCommodityCount(int $wantedCommodityCount): TradeOfferInterface
    {
        $this->wg_count = $wantedCommodityCount;

        return $this;
    }

    public function getOfferedCommodityId(): int
    {
        return $this->gg_id;
    }

    public function setOfferedCommodityId(int $offeredCommodityId): TradeOfferInterface
    {
        $this->gg_id = $offeredCommodityId;

        return $this;
    }

    public function getOfferedCommodityCount(): int
    {
        return $this->gg_count;
    }

    public function setOfferedCommodityCount(int $offeredCommodityCount): TradeOfferInterface
    {
        $this->gg_count = $offeredCommodityCount;

        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): TradeOfferInterface
    {
        $this->date = $date;

        return $this;
    }

    public function getTradePost(): TradePostInterface
    {
        return $this->tradePost;
    }

    public function setTradePost(TradePostInterface $tradePost): TradeOfferInterface
    {
        $this->tradePost = $tradePost;

        return $this;
    }

    public function getWantedCommodity(): CommodityInterface
    {
        return $this->wantedCommodity;
    }

    public function setWantedCommodity(CommodityInterface $wantedCommodity): TradeOfferInterface
    {
        $this->wantedCommodity = $wantedCommodity;

        return $this;
    }

    public function getOfferedCommodity(): CommodityInterface
    {
        return $this->offeredCommodity;
    }

    public function setOfferedCommodity(CommodityInterface $offeredCommodity): TradeOfferInterface
    {
        $this->offeredCommodity = $offeredCommodity;

        return $this;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): TradeOfferInterface
    {
        $this->user = $user;
        return $this;
    }

    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }
}
