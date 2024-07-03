<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Orm\Repository\TradeOfferRepository;
use Override;
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
#[Entity(repositoryClass: TradeOfferRepository::class)]
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

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getUserId(): int
    {
        return $this->user_id;
    }

    #[Override]
    public function getTradePostId(): int
    {
        return $this->posts_id;
    }

    #[Override]
    public function setTradePostId(int $tradePostId): TradeOfferInterface
    {
        $this->posts_id = $tradePostId;

        return $this;
    }

    #[Override]
    public function getOfferCount(): int
    {
        return $this->amount;
    }

    #[Override]
    public function setOfferCount(int $offerCount): TradeOfferInterface
    {
        $this->amount = $offerCount;

        return $this;
    }

    #[Override]
    public function getWantedCommodityId(): int
    {
        return $this->wg_id;
    }

    #[Override]
    public function setWantedCommodityId(int $wantedCommodityId): TradeOfferInterface
    {
        $this->wg_id = $wantedCommodityId;

        return $this;
    }

    #[Override]
    public function getWantedCommodityCount(): int
    {
        return $this->wg_count;
    }

    #[Override]
    public function setWantedCommodityCount(int $wantedCommodityCount): TradeOfferInterface
    {
        $this->wg_count = $wantedCommodityCount;

        return $this;
    }

    #[Override]
    public function getOfferedCommodityId(): int
    {
        return $this->gg_id;
    }

    #[Override]
    public function setOfferedCommodityId(int $offeredCommodityId): TradeOfferInterface
    {
        $this->gg_id = $offeredCommodityId;

        return $this;
    }

    #[Override]
    public function getOfferedCommodityCount(): int
    {
        return $this->gg_count;
    }

    #[Override]
    public function setOfferedCommodityCount(int $offeredCommodityCount): TradeOfferInterface
    {
        $this->gg_count = $offeredCommodityCount;

        return $this;
    }

    #[Override]
    public function getDate(): int
    {
        return $this->date;
    }

    #[Override]
    public function setDate(int $date): TradeOfferInterface
    {
        $this->date = $date;

        return $this;
    }

    #[Override]
    public function getTradePost(): TradePostInterface
    {
        return $this->tradePost;
    }

    #[Override]
    public function setTradePost(TradePostInterface $tradePost): TradeOfferInterface
    {
        $this->tradePost = $tradePost;

        return $this;
    }

    #[Override]
    public function getWantedCommodity(): CommodityInterface
    {
        return $this->wantedCommodity;
    }

    #[Override]
    public function setWantedCommodity(CommodityInterface $wantedCommodity): TradeOfferInterface
    {
        $this->wantedCommodity = $wantedCommodity;

        return $this;
    }

    #[Override]
    public function getOfferedCommodity(): CommodityInterface
    {
        return $this->offeredCommodity;
    }

    #[Override]
    public function setOfferedCommodity(CommodityInterface $offeredCommodity): TradeOfferInterface
    {
        $this->offeredCommodity = $offeredCommodity;

        return $this;
    }

    #[Override]
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    #[Override]
    public function setUser(UserInterface $user): TradeOfferInterface
    {
        $this->user = $user;
        return $this;
    }

    #[Override]
    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }
}
