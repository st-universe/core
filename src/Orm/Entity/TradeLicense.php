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
use Stu\Component\Game\TimeConstants;
use Stu\Module\Control\StuTime;
use Stu\Orm\Repository\TradeLicenseRepository;

#[Table(name: 'stu_trade_license')]
#[Index(name: 'user_trade_post_idx', columns: ['user_id', 'posts_id'])]
#[Entity(repositoryClass: TradeLicenseRepository::class)]
class TradeLicense
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
    private int $date = 0;

    #[Column(type: 'integer')]
    private int $expired = 0;

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

    public function setTradePostId(int $tradePostId): TradeLicense
    {
        $this->posts_id = $tradePostId;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): TradeLicense
    {
        $this->date = $date;

        return $this;
    }

    public function getExpired(): int
    {
        return $this->expired;
    }

    public function setExpired(int $expired): TradeLicense
    {
        $this->expired = $expired;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): TradeLicense
    {
        $this->user = $user;
        return $this;
    }

    public function getTradePost(): TradePost
    {
        return $this->tradePost;
    }

    public function setTradePost(TradePost $tradePost): TradeLicense
    {
        $this->tradePost = $tradePost;

        return $this;
    }

    public function getRemainingFullDays(?StuTime $stuTime = null): int
    {
        return (int)floor(($this->getExpired() - ($stuTime !== null ? $stuTime->time() : time())) / TimeConstants::ONE_DAY_IN_SECONDS);
    }
}
