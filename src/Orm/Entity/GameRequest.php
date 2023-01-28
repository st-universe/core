<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\GameRequestRepository")
 * @Table(
 *     name="stu_game_request",
 *     indexes={
 *          @Index(name="game_request_idx",columns={"user_id", "action", "view"})
 *     }
 * )
 **/
class GameRequest implements GameRequestInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     *
     * @var int
     */
    private $id;

    /**
     * @Column(type="integer", nullable=true)
     *
     * @var int|null
     */
    private $user_id;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $turn_id;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $time;

    /**
     * @Column(type="string")
     *
     * @var string
     */
    private $module;

    /**
     * @Column(type="string", nullable=true)
     *
     * @var null|string
     */
    private $action;

    /**
     * @Column(type="integer", nullable=true)
     *
     * @var int|null
     */
    private $action_ms;

    /**
     * @Column(type="string", nullable=true)
     *
     * @var string|null
     */
    private $view;

    /**
     * @Column(type="integer", nullable=true)
     *
     * @var int|null
     */
    private $view_ms;

    /**
     * @Column(type="integer", nullable=true)
     *
     * @var int|null
     */
    private $render_ms;

    /**
     * @Column(type="text", nullable=true)
     *
     * @var string|null
     */
    private $params;

    /** @var null|array<mixed> */
    private $parameterArray;

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

    public function setTurnId(GameTurnInterface $turn): GameRequestInterface
    {
        $this->turn_id = $turn->getId();
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

    public function setParams(): GameRequestInterface
    {
        $this->unsetParameter('_');
        $this->unsetParameter('sstr');
        $this->unsetParameter('login');
        $this->unsetParameter('pass');
        $this->unsetParameter('pass2');
        $this->unsetParameter('oldpass');
        if ($this->parameterArray !== null && !empty($this->parameterArray)) {
            $string = print_r($this->parameterArray, true);
            $this->params = substr($string, 8, strlen($string) - 11);
        }
        return $this;
    }

    public function setParameterArray(array $array): GameRequestInterface
    {
        $this->parameterArray = $array;
        return $this;
    }

    public function unsetParameter($key): void
    {
        unset($this->parameterArray[$key]);
    }
}
