<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Attribute\TruncateOnGameReset;
use Stu\Orm\Repository\GameTurnRepository;

#[Table(name: 'stu_game_turns')]
#[Index(name: 'turn_idx', columns: ['turn'])]
#[Entity(repositoryClass: GameTurnRepository::class)]
#[TruncateOnGameReset]
class GameTurn
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $turn = 0;

    #[Column(type: 'integer')]
    private int $startdate = 0;

    #[Column(type: 'integer')]
    private int $enddate;

    #[Column(type: 'integer', nullable: true)]
    private ?int $pirate_fleets = 0;

    #[OneToOne(targetEntity: GameTurnStats::class, mappedBy: 'turn')]
    private ?GameTurnStats $stats = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getTurn(): int
    {
        return $this->turn;
    }

    public function setTurn(int $turn): GameTurn
    {
        $this->turn = $turn;

        return $this;
    }

    public function getStart(): int
    {
        return $this->startdate;
    }

    public function setStart(int $start): GameTurn
    {
        $this->startdate = $start;

        return $this;
    }

    public function getEnd(): int
    {
        return $this->enddate;
    }

    public function setEnd(int $end): GameTurn
    {
        $this->enddate = $end;

        return $this;
    }

    public function getStats(): ?GameTurnStats
    {
        return $this->stats;
    }

    public function getPirateFleets(): int
    {
        return $this->pirate_fleets ?? 0;
    }

    public function setPirateFleets(int $pirateFleets): GameTurn
    {
        $this->pirate_fleets = $pirateFleets;

        return $this;
    }
}
