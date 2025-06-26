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
use Stu\Orm\Repository\ShipTakeoverRepository;

#[Table(name: 'stu_ship_takeover')]
#[Index(name: 'ship_takeover_source_idx', columns: ['source_spacecraft_id'])]
#[Index(name: 'ship_takeover_target_idx', columns: ['target_spacecraft_id'])]
#[Entity(repositoryClass: ShipTakeoverRepository::class)]
class ShipTakeover
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $source_spacecraft_id;

    #[Column(type: 'integer')]
    private int $target_spacecraft_id;

    #[Column(type: 'integer')]
    private int $start_turn = 0;

    #[Column(type: 'integer')]
    private int $prestige = 0;

    #[OneToOne(targetEntity: Spacecraft::class)]
    #[JoinColumn(name: 'source_spacecraft_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Spacecraft $source;

    #[OneToOne(targetEntity: Spacecraft::class)]
    #[JoinColumn(name: 'target_spacecraft_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Spacecraft $target;

    public function getId(): int
    {
        return $this->id;
    }

    public function setSourceSpacecraft(Spacecraft $spacecraft): ShipTakeover
    {
        $this->source = $spacecraft;

        return $this;
    }

    public function getSourceSpacecraft(): Spacecraft
    {
        return $this->source;
    }

    public function setTargetSpacecraft(Spacecraft $spacecraft): ShipTakeover
    {
        $this->target = $spacecraft;

        return $this;
    }

    public function getTargetSpacecraft(): Spacecraft
    {
        return $this->target;
    }

    public function getStartTurn(): int
    {
        return $this->start_turn;
    }

    public function setStartTurn(int $turn): ShipTakeover
    {
        $this->start_turn = $turn;
        return $this;
    }

    public function getPrestige(): int
    {
        return $this->prestige;
    }

    public function setPrestige(int $prestige): ShipTakeover
    {
        $this->prestige = $prestige;
        return $this;
    }
}
