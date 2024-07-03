<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Override;
use Stu\Orm\Repository\ShipTakeoverRepository;

#[Table(name: 'stu_ship_takeover')]
#[Index(name: 'ship_takeover_source_idx', columns: ['source_ship_id'])]
#[Index(name: 'ship_takeover_target_idx', columns: ['target_ship_id'])]
#[Entity(repositoryClass: ShipTakeoverRepository::class)]
class ShipTakeover implements ShipTakeoverInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $source_ship_id;

    #[Column(type: 'integer')]
    private int $target_ship_id;

    #[Column(type: 'integer')]
    private int $start_turn = 0;

    #[Column(type: 'integer')]
    private int $prestige = 0;

    #[OneToOne(targetEntity: 'Ship')]
    #[JoinColumn(name: 'source_ship_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ShipInterface $source;

    #[OneToOne(targetEntity: 'Ship')]
    #[JoinColumn(name: 'target_ship_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ShipInterface $target;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function setSourceShip(ShipInterface $ship): ShipTakeoverInterface
    {
        $this->source = $ship;

        return $this;
    }

    #[Override]
    public function getSourceShip(): ShipInterface
    {
        return $this->source;
    }

    #[Override]
    public function setTargetShip(ShipInterface $ship): ShipTakeoverInterface
    {
        $this->target = $ship;

        return $this;
    }

    #[Override]
    public function getTargetShip(): ShipInterface
    {
        return $this->target;
    }

    #[Override]
    public function getStartTurn(): int
    {
        return $this->start_turn;
    }

    #[Override]
    public function setStartTurn(int $turn): ShipTakeoverInterface
    {
        $this->start_turn = $turn;
        return $this;
    }

    #[Override]
    public function getPrestige(): int
    {
        return $this->prestige;
    }

    #[Override]
    public function setPrestige(int $prestige): ShipTakeoverInterface
    {
        $this->prestige = $prestige;
        return $this;
    }
}
