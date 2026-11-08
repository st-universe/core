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
use InvalidArgumentException;
use Stu\Lib\Transfer\Storage\ModuleQualityPacket;
use Stu\Orm\Repository\AuctionBidRepository;

#[Table(name: 'stu_auction_bid')]
#[Index(name: 'auction_bid_sort_idx', columns: ['max_amount'])]
#[Entity(repositoryClass: AuctionBidRepository::class)]
class AuctionBid
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $auction_id;

    #[Column(type: 'integer')]
    private int $user_id;

    #[Column(type: 'integer')]
    private int $max_amount;

    /** @var array<int, int> */
    #[Column(name: 'module_quality_amounts', type: 'json', options: ['default' => '{}'])]
    private array $moduleQualityAmounts = [];

    #[ManyToOne(targetEntity: Deals::class)]
    #[JoinColumn(name: 'auction_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Deals $auction;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private User $user;

    /** @return array<int, int> */
    public function getModuleQualityAmounts(): array
    {
        return $this->moduleQualityAmounts === [] ? [0 => $this->getMaxAmount()] : $this->moduleQualityAmounts;
    }

    /** @param array<int, int> $qualities */
    public function setModuleQualityAmounts(array $qualities): self
    {
        if (ModuleQualityPacket::validate($qualities) !== $this->getMaxAmount()) {
            throw new InvalidArgumentException('Bid quality amounts must match the reserved amount');
        }
        $this->moduleQualityAmounts = $qualities;
        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAuctionId(): int
    {
        return $this->auction_id;
    }


    public function setAuctionId(int $auction_id): AuctionBid
    {
        $this->auction_id = $auction_id;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }


    public function setUserId(int $user_id): AuctionBid
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getMaxAmount(): int
    {
        return $this->max_amount;
    }

    public function setMaxAmount(int $max_amount): AuctionBid
    {
        $this->max_amount = $max_amount;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): AuctionBid
    {
        $this->user = $user;

        return $this;
    }

    public function getAuction(): Deals
    {
        return $this->auction;
    }

    public function setAuction(Deals $auction): AuctionBid
    {
        $this->auction = $auction;

        return $this;
    }
}
