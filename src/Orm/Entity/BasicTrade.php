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
use Stu\Orm\Repository\BasicTradeRepository;

#[Table(name: 'stu_basic_trade')]
#[Index(name: 'base_trade_idx', columns: ['faction_id', 'commodity_id', 'date_ms'])]
#[Entity(repositoryClass: BasicTradeRepository::class)]
class BasicTrade implements BasicTradeInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer', nullable: true)]
    private ?int $faction_id = null;

    #[Column(type: 'integer')]
    private int $commodity_id = 0;

    #[Column(type: 'smallint')]
    private int $buy_sell = 0;

    #[Column(type: 'integer')]
    private int $value = 0;

    #[Column(type: 'bigint', nullable: true)]
    private ?int $date_ms = null;

    #[Column(type: 'string')]
    private string $uniqid;

    #[Column(type: 'integer', nullable: true)]
    private ?int $user_id = null;

    #[ManyToOne(targetEntity: 'Faction')]
    #[JoinColumn(name: 'faction_id', referencedColumnName: 'id')]
    private FactionInterface $faction;

    #[ManyToOne(targetEntity: 'Commodity')]
    #[JoinColumn(name: 'commodity_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private CommodityInterface $commodity;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function setFaction(FactionInterface $faction): BasicTradeInterface
    {
        $this->faction = $faction;

        return $this;
    }

    #[Override]
    public function getFaction(): FactionInterface
    {
        return $this->faction;
    }

    #[Override]
    public function setCommodity(CommodityInterface $commodity): BasicTradeInterface
    {
        $this->commodity = $commodity;

        return $this;
    }

    #[Override]
    public function getCommodity(): CommodityInterface
    {
        return $this->commodity;
    }

    #[Override]
    public function setBuySell(int $buySell): BasicTradeInterface
    {
        $this->buy_sell = $buySell;

        return $this;
    }

    #[Override]
    public function getBuySell(): int
    {
        return $this->buy_sell;
    }

    #[Override]
    public function setValue(int $value): BasicTradeInterface
    {
        $this->value = $value;

        return $this;
    }

    #[Override]
    public function getValue(): int
    {
        return $this->value;
    }

    #[Override]
    public function setDate(int $date): BasicTradeInterface
    {
        $this->date_ms = $date;

        return $this;
    }

    #[Override]
    public function getDate(): int
    {
        return (int)$this->date_ms;
    }

    #[Override]
    public function setUniqId(string $uniqid): BasicTradeInterface
    {
        $this->uniqid = $uniqid;

        return $this;
    }

    #[Override]
    public function getUniqId(): string
    {
        return $this->uniqid;
    }

    #[Override]
    public function setUserId(int $userId): BasicTradeInterface
    {
        $this->user_id = $userId;

        return $this;
    }
}
