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
use Override;
use Stu\Orm\Repository\GameTurnRepository;

#[Table(name: 'stu_game_turns')]
#[Index(name: 'turn_idx', columns: ['turn'])]
#[Entity(repositoryClass: GameTurnRepository::class)]
class GameTurn implements GameTurnInterface
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

    #[OneToOne(targetEntity: 'GameTurnStats', mappedBy: 'turn')]
    private ?GameTurnStatsInterface $stats = null;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getTurn(): int
    {
        return $this->turn;
    }

    #[Override]
    public function setTurn(int $turn): GameTurnInterface
    {
        $this->turn = $turn;

        return $this;
    }

    #[Override]
    public function getStart(): int
    {
        return $this->startdate;
    }

    #[Override]
    public function setStart(int $start): GameTurnInterface
    {
        $this->startdate = $start;

        return $this;
    }

    #[Override]
    public function getEnd(): int
    {
        return $this->enddate;
    }

    #[Override]
    public function setEnd(int $end): GameTurnInterface
    {
        $this->enddate = $end;

        return $this;
    }

    #[Override]
    public function getStats(): ?GameTurnStatsInterface
    {
        return $this->stats;
    }

    #[Override]
    public function getPirateFleets(): int
    {
        return $this->pirate_fleets ?? 0;
    }

    #[Override]
    public function setPirateFleets(int $pirateFleets): GameTurnInterface
    {
        $this->pirate_fleets = $pirateFleets;

        return $this;
    }
}
