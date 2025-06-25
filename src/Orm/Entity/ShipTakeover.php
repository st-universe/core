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
#[Index(name: 'ship_takeover_source_idx', columns: ['source_spacecraft_id'])]
#[Index(name: 'ship_takeover_target_idx', columns: ['target_spacecraft_id'])]
#[Entity(repositoryClass: ShipTakeoverRepository::class)]
class ShipTakeover implements ShipTakeoverInterface
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
    private SpacecraftInterface $source;

    #[OneToOne(targetEntity: Spacecraft::class)]
    #[JoinColumn(name: 'target_spacecraft_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private SpacecraftInterface $target;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function setSourceSpacecraft(SpacecraftInterface $spacecraft): ShipTakeoverInterface
    {
        $this->source = $spacecraft;

        return $this;
    }

    #[Override]
    public function getSourceSpacecraft(): SpacecraftInterface
    {
        return $this->source;
    }

    #[Override]
    public function setTargetSpacecraft(SpacecraftInterface $spacecraft): ShipTakeoverInterface
    {
        $this->target = $spacecraft;

        return $this;
    }

    #[Override]
    public function getTargetSpacecraft(): SpacecraftInterface
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
