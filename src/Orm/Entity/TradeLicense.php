<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Override;
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

#[Table(name: 'stu_trade_license')]
#[Index(name: 'user_trade_post_idx', columns: ['user_id', 'posts_id'])]
#[Entity(repositoryClass: 'Stu\Orm\Repository\TradeLicenseRepository')]
class TradeLicense implements TradeLicenseInterface
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

    #[ManyToOne(targetEntity: 'TradePost')]
    #[JoinColumn(name: 'posts_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private TradePostInterface $tradePost;

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getTradePostId(): int
    {
        return $this->posts_id;
    }

    #[Override]
    public function setTradePostId(int $tradePostId): TradeLicenseInterface
    {
        $this->posts_id = $tradePostId;

        return $this;
    }

    #[Override]
    public function getUserId(): int
    {
        return $this->user_id;
    }

    #[Override]
    public function getDate(): int
    {
        return $this->date;
    }

    #[Override]
    public function setDate(int $date): TradeLicenseInterface
    {
        $this->date = $date;

        return $this;
    }

    #[Override]
    public function getExpired(): int
    {
        return $this->expired;
    }

    #[Override]
    public function setExpired(int $expired): TradeLicenseInterface
    {
        $this->expired = $expired;

        return $this;
    }

    #[Override]
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    #[Override]
    public function setUser(UserInterface $user): TradeLicenseInterface
    {
        $this->user = $user;
        return $this;
    }

    #[Override]
    public function getTradePost(): TradePostInterface
    {
        return $this->tradePost;
    }

    #[Override]
    public function setTradePost(TradePostInterface $tradePost): TradeLicenseInterface
    {
        $this->tradePost = $tradePost;

        return $this;
    }

    #[Override]
    public function getRemainingFullDays(?StuTime $stuTime = null): int
    {
        return (int)floor(($this->getExpired() - ($stuTime !== null ? $stuTime->time() : time())) / TimeConstants::ONE_DAY_IN_SECONDS);
    }
}
