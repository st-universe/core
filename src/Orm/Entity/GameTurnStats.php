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
use Stu\Orm\Repository\GameTurnStatsRepository;

#[Table(name: 'stu_game_turn_stats')]
#[Index(name: 'game_turn_stats_turn_idx', columns: ['turn_id'])]
#[Entity(repositoryClass: GameTurnStatsRepository::class)]
class GameTurnStats
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $turn_id;

    #[Column(type: 'integer')]
    private int $user_count;

    #[Column(type: 'integer')]
    private int $logins_24h;

    #[Column(type: 'integer')]
    private int $inactive_count;

    #[Column(type: 'integer')]
    private int $vacation_count;

    #[Column(type: 'integer')]
    private int $ship_count;

    #[Column(type: 'integer')]
    private int $ship_count_manned;

    #[Column(type: 'integer')]
    private int $ship_count_npc;

    #[Column(type: 'integer')]
    private int $kn_count;

    #[Column(type: 'integer')]
    private int $flight_sig_24h;

    #[Column(type: 'integer')]
    private int $flight_sig_system_24h;

    #[Column(type: 'integer')]
    private int $new_pm_count;

    #[OneToOne(targetEntity: GameTurn::class, inversedBy: 'stats')]
    #[JoinColumn(name: 'turn_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private GameTurn $turn;

    public function getId(): int
    {
        return $this->id;
    }

    public function getTurn(): GameTurn
    {
        return $this->turn;
    }

    public function setTurn(GameTurn $turn): GameTurnStats
    {
        $this->turn = $turn;
        return $this;
    }

    public function getUserCount(): int
    {
        return $this->user_count;
    }

    public function setUserCount(int $userCount): GameTurnStats
    {
        $this->user_count = $userCount;

        return $this;
    }

    public function getLogins24h(): int
    {
        return $this->logins_24h;
    }

    public function setLogins24h(int $logins24h): GameTurnStats
    {
        $this->logins_24h = $logins24h;

        return $this;
    }

    public function getInactiveCount(): int
    {
        return $this->inactive_count;
    }

    public function setInactiveCount(int $inactiveCount): GameTurnStats
    {
        $this->inactive_count = $inactiveCount;

        return $this;
    }

    public function getVacationCount(): int
    {
        return $this->vacation_count;
    }

    public function setVacationCount(int $vacationCount): GameTurnStats
    {
        $this->vacation_count = $vacationCount;

        return $this;
    }

    public function getShipCount(): int
    {
        return $this->ship_count;
    }

    public function setShipCount(int $shipCount): GameTurnStats
    {
        $this->ship_count = $shipCount;

        return $this;
    }

    public function getShipCountManned(): int
    {
        return $this->ship_count_manned;
    }

    public function setShipCountManned(int $shipCountManned): GameTurnStats
    {
        $this->ship_count_manned = $shipCountManned;

        return $this;
    }

    public function getShipCountNpc(): int
    {
        return $this->ship_count_npc;
    }

    public function setShipCountNpc(int $shipCountNpc): GameTurnStats
    {
        $this->ship_count_npc = $shipCountNpc;

        return $this;
    }

    public function getKnCount(): int
    {
        return $this->kn_count;
    }

    public function setKnCount(int $knCount): GameTurnStats
    {
        $this->kn_count = $knCount;

        return $this;
    }

    public function getFlightSig24h(): int
    {
        return $this->flight_sig_24h;
    }

    public function setFlightSig24h(int $flightSig24h): GameTurnStats
    {
        $this->flight_sig_24h = $flightSig24h;

        return $this;
    }

    public function getFlightSigSystem24h(): int
    {
        return $this->flight_sig_system_24h;
    }

    public function setFlightSigSystem24h(int $flightSigSystem24h): GameTurnStats
    {
        $this->flight_sig_system_24h = $flightSigSystem24h;

        return $this;
    }

    public function getNewPmCount(): int
    {
        return $this->new_pm_count;
    }

    public function setNewPmCount(int $newPmCount): GameTurnStats
    {
        $this->new_pm_count = $newPmCount;

        return $this;
    }

    public function __toString(): string
    {
        return sprintf(
            'id: %s, turnId: %s',
            $this->id,
            $this->turn_id
        );
    }
}
