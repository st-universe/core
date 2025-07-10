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
use Stu\Orm\Attribute\TruncateOnGameReset;
use Stu\Orm\Repository\BasicTradeRepository;

#[Table(name: 'stu_basic_trade')]
#[Index(name: 'base_trade_idx', columns: ['faction_id', 'commodity_id', 'date_ms'])]
#[Entity(repositoryClass: BasicTradeRepository::class)]
#[TruncateOnGameReset]
class BasicTrade
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

    #[ManyToOne(targetEntity: Faction::class)]
    #[JoinColumn(name: 'faction_id', nullable: false, referencedColumnName: 'id')]
    private Faction $faction;

    #[ManyToOne(targetEntity: Commodity::class)]
    #[JoinColumn(name: 'commodity_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Commodity $commodity;

    public function getId(): int
    {
        return $this->id;
    }

    public function setFaction(Faction $faction): BasicTrade
    {
        $this->faction = $faction;

        return $this;
    }

    public function getFaction(): Faction
    {
        return $this->faction;
    }

    public function setCommodity(Commodity $commodity): BasicTrade
    {
        $this->commodity = $commodity;

        return $this;
    }

    public function getCommodity(): Commodity
    {
        return $this->commodity;
    }

    public function setBuySell(int $buySell): BasicTrade
    {
        $this->buy_sell = $buySell;

        return $this;
    }

    public function getBuySell(): int
    {
        return $this->buy_sell;
    }

    public function setValue(int $value): BasicTrade
    {
        $this->value = $value;

        return $this;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function setDate(int $date): BasicTrade
    {
        $this->date_ms = $date;

        return $this;
    }

    public function getDate(): int
    {
        return (int)$this->date_ms;
    }

    public function setUniqId(string $uniqid): BasicTrade
    {
        $this->uniqid = $uniqid;

        return $this;
    }

    public function getUniqId(): string
    {
        return $this->uniqid;
    }

    public function setUserId(int $userId): BasicTrade
    {
        $this->user_id = $userId;

        return $this;
    }
}
