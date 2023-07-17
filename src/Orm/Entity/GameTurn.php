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

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\GameTurnRepository")
 * @Table(
 *     name="stu_game_turns",
 *     indexes={
 *         @Index(name="turn_idx", columns={"turn"})
 *     }
 * )
 **/
class GameTurn implements GameTurnInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     *
     */
    private int $id;

    /**
     * @Column(type="integer")
     *
     */
    private int $turn = 0;

    /**
     * @Column(type="integer")
     *
     */
    private int $startdate = 0;

    /**
     * @Column(type="integer")
     *
     */
    private int $enddate;

    /**
     * @OneToOne(targetEntity="GameTurnStats", mappedBy="turn")
     */
    private ?GameTurnStatsInterface $stats = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getTurn(): int
    {
        return $this->turn;
    }

    public function setTurn(int $turn): GameTurnInterface
    {
        $this->turn = $turn;

        return $this;
    }

    public function getStart(): int
    {
        return $this->startdate;
    }

    public function setStart(int $start): GameTurnInterface
    {
        $this->startdate = $start;

        return $this;
    }

    public function getEnd(): int
    {
        return $this->enddate;
    }

    public function setEnd(int $end): GameTurnInterface
    {
        $this->enddate = $end;

        return $this;
    }

    public function getStats(): ?GameTurnStatsInterface
    {
        return $this->stats;
    }
}
