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
use Stu\Orm\Repository\TradeTransferRepository;

#[Table(name: 'stu_trade_transfers')]
#[Index(name: 'trade_transfer_post_user_idx', columns: ['posts_id', 'user_id'])]
#[Entity(repositoryClass: TradeTransferRepository::class)]
class TradeTransfer
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $posts_id = 0;

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'integer')]
    private int $count = 0;

    #[Column(type: 'integer')]
    private int $date = 0;

    #[ManyToOne(targetEntity: TradePost::class)]
    #[JoinColumn(name: 'posts_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private TradePost $tradePost;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    public function getId(): int
    {
        return $this->id;
    }

    public function getTradePostId(): int
    {
        return $this->posts_id;
    }

    public function setTradePostId(int $tradePostId): TradeTransfer
    {
        $this->posts_id = $tradePostId;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): TradeTransfer
    {
        $this->user = $user;
        return $this;
    }

    public function getAmount(): int
    {
        return $this->count;
    }

    public function setAmount(int $amount): TradeTransfer
    {
        $this->count = $amount;

        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): TradeTransfer
    {
        $this->date = $date;

        return $this;
    }

    public function getTradePost(): TradePost
    {
        return $this->tradePost;
    }

    public function setTradePost(TradePost $tradePost): TradeTransfer
    {
        $this->tradePost = $tradePost;

        return $this;
    }
}
