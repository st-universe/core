<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\GameRequestRepository")
 * @Table(
 *     name="stu_game_request"
 * )
 **/
class GameRequest implements GameRequestInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="integer", nullable=true) * */
    private $user_id;

    /** @Column(type="integer") * */
    private $turn_id;

    /** @Column(type="integer") * */
    private $time;

    /** @Column(type="string") * */
    private $module;

    /** @Column(type="string", nullable=true) * */
    private $action;

    /** @Column(type="integer") * */
    private $action_ms;

    /** @Column(type="string", nullable=true) * */
    private $view;

    /** @Column(type="integer") * */
    private $view_ms;

    /** @Column(type="integer", nullable=true) * */
    private $render_ms;

    /**
     * @ManyToOne(targetEntity="GameTurn")
     * @JoinColumn(name="turn_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $turn;

    public function getId(): int
    {
        return $this->id;
    }

    public function setUserId(?UserInterface $user): GameRequestInterface
    {
        if ($user !== null) {
            $this->user_id = $user->getId();
        }
        return $this;
    }

    public function getTurn(): GameTurnInterface
    {
        return $this->turn;
    }

    public function setTurn(GameTurnInterface $turn): GameRequestInterface
    {
        $this->turn = $turn;
        return $this;
    }

    public function setTime(int $time): GameRequestInterface
    {
        $this->time = $time;
        return $this;
    }

    public function setModule(string $module): GameRequestInterface
    {
        $this->module = $module;
        return $this;
    }

    public function setAction(?string $action): GameRequestInterface
    {
        $this->action = $action;
        return $this;
    }

    public function setActionMs(int $actionMs): GameRequestInterface
    {
        $this->action_ms = $actionMs;
        return $this;
    }

    public function setView(?string $view): GameRequestInterface
    {
        $this->view = $view;
        return $this;
    }

    public function setViewMs(int $viewMs): GameRequestInterface
    {
        $this->view_ms = $viewMs;
        return $this;
    }

    public function setRenderMs(int $renderMs): GameRequestInterface
    {
        $this->render_ms = $renderMs;
        return $this;
    }
}
