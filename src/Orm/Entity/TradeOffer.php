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
use LogicException;
use Stu\Orm\Repository\TradeOfferRepository;

#[Table(name: 'stu_trade_offers')]
#[Index(name: 'trade_post_user_idx', columns: ['posts_id', 'user_id'])]
#[Entity(repositoryClass: TradeOfferRepository::class)]
class TradeOffer
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

    #[ManyToOne(targetEntity: TradePost::class)]
    #[JoinColumn(name: 'posts_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private TradePost $tradePost;

    #[ManyToOne(targetEntity: Commodity::class)]
    #[JoinColumn(name: 'wg_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Commodity $wantedCommodity;

    #[ManyToOne(targetEntity: Commodity::class)]
    #[JoinColumn(name: 'gg_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Commodity $offeredCommodity;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    #[OneToOne(targetEntity: Storage::class, mappedBy: 'tradeOffer')]
    private ?Storage $storage;

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

    public function setTradePostId(int $tradePostId): TradeOffer
    {
        $this->posts_id = $tradePostId;

        return $this;
    }

    public function getOfferCount(): int
    {
        return $this->amount;
    }

    public function setOfferCount(int $offerCount): TradeOffer
    {
        $this->amount = $offerCount;

        return $this;
    }

    public function getWantedCommodityId(): int
    {
        return $this->wg_id;
    }

    public function setWantedCommodityId(int $wantedCommodityId): TradeOffer
    {
        $this->wg_id = $wantedCommodityId;

        return $this;
    }

    public function getWantedCommodityCount(): int
    {
        return $this->wg_count;
    }

    public function setWantedCommodityCount(int $wantedCommodityCount): TradeOffer
    {
        $this->wg_count = $wantedCommodityCount;

        return $this;
    }

    public function getOfferedCommodityId(): int
    {
        return $this->gg_id;
    }

    public function setOfferedCommodityId(int $offeredCommodityId): TradeOffer
    {
        $this->gg_id = $offeredCommodityId;

        return $this;
    }

    public function getOfferedCommodityCount(): int
    {
        return $this->gg_count;
    }

    public function setOfferedCommodityCount(int $offeredCommodityCount): TradeOffer
    {
        $this->gg_count = $offeredCommodityCount;

        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): TradeOffer
    {
        $this->date = $date;

        return $this;
    }

    public function getTradePost(): TradePost
    {
        return $this->tradePost;
    }

    public function setTradePost(TradePost $tradePost): TradeOffer
    {
        $this->tradePost = $tradePost;

        return $this;
    }

    public function getWantedCommodity(): Commodity
    {
        return $this->wantedCommodity;
    }

    public function setWantedCommodity(Commodity $wantedCommodity): TradeOffer
    {
        $this->wantedCommodity = $wantedCommodity;

        return $this;
    }

    public function getOfferedCommodity(): Commodity
    {
        return $this->offeredCommodity;
    }

    public function setOfferedCommodity(Commodity $offeredCommodity): TradeOffer
    {
        $this->offeredCommodity = $offeredCommodity;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): TradeOffer
    {
        $this->user = $user;
        return $this;
    }

    public function getStorage(): Storage
    {
        return $this->storage ?? throw new LogicException('TradeOffer has no storage');
    }
}
