<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

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
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="integer") * */
    private $turn = 0;

    /** @Column(type="integer") * */
    private $start = 0;

    /** @Column(type="integer") * */
    private $end;

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
        return $this->start;
    }

    public function setStart(int $start): GameTurnInterface
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): int
    {
        return $this->end;
    }

    public function setEnd(int $end): GameTurnInterface
    {
        $this->end = $end;

        return $this;
    }
}
