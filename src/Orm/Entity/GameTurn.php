<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\GameTurnRepository")
 * @Table(
 *     name="stu_game_turns",
 *     indexes={
 *          @Index(name="turn_idx",columns={"turn"})
 *     }
 * )
 **/
class GameTurn implements GameTurnInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="integer") * */
    private $turn = 0;

    /** @Column(type="integer") * */
    private $startdate = 0;

    /** @Column(type="integer") * */
    private $enddate;

    /**
     * @var null|GameTurnStatsInterface
     *
     * @OneToOne(targetEntity="GameTurnStats", mappedBy="turn")
     */
    private $stats;

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

    public function setStart(int $startdate): GameTurnInterface
    {
        $this->startdate = $startdate;

        return $this;
    }

    public function getEnd(): int
    {
        return $this->enddate;
    }

    public function setEnd(int $enddate): GameTurnInterface
    {
        $this->enddate = $enddate;

        return $this;
    }

    public function getStats(): ?GameTurnStatsInterface
    {
        return $this->stats;
    }
}
