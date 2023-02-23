<?php

declare(strict_types=1);

namespace Stu\Component\Game;

use Stu\Game\GameRequestInterface;
use Stu\Orm\Entity\GameTurnInterface;
use Stu\Orm\Entity\UserInterface;
use Throwable;

class GameRequest implements GameRequestInterface
{
    /**
     * @var int|null
     */
    private $user_id;

    /**
     * @var int
     */
    private $turn_id;

    /**
     * @var int
     */
    private $time;

    /**
     * @var string
     */
    private $module;

    /**
     * @var null|string
     */
    private $action;

    /**
     * @var int|null
     */
    private $action_ms;

    /**
     * @var string|null
     */
    private $view;

    /**
     * @var int|null
     */
    private $view_ms;

    /**
     * @var int|null
     */
    private $render_ms;

    /** @var array<mixed> */
    private array $parameter = [];

    private string $requestId = '';

    /** @var array<Throwable> */
    private array $errors = [];

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

    public function setAction(string $action): GameRequestInterface
    {
        $this->action = $action;
        $this->unsetParameter($action);

        return $this;
    }

    public function setActionMs(int $actionMs): GameRequestInterface
    {
        $this->action_ms = $actionMs;
        return $this;
    }

    public function setView(string $view): GameRequestInterface
    {
        $this->view = $view;
        $this->unsetParameter($view);

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

    public function setParameter(array $parameter): GameRequestInterface
    {
        $this->parameter = $parameter;
        return $this;
    }

    public function getParameter(): array
    {
        return $this->parameter;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function getTurnId(): int
    {
        return $this->turn_id;
    }

    public function getTime(): int
    {
        return $this->time;
    }

    public function getModule(): string
    {
        return $this->module;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function getActionMs(): ?int
    {
        return $this->action_ms;
    }

    public function getView(): ?string
    {
        return $this->view;
    }

    public function getViewMs(): ?int
    {
        return $this->view_ms;
    }

    public function getRenderMs(): ?int
    {
        return $this->render_ms;
    }

    public function getRequestId(): string
    {
        return $this->requestId;
    }

    public function setRequestId(string $requestId): GameRequestInterface
    {
        $this->requestId = $requestId;

        return $this;
    }

    public function addError(Throwable $error): GameRequestInterface
    {
        $this->errors[] = $error;

        return $this;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    private function unsetParameter(string $key): void
    {
        unset($this->parameter[$key]);
    }
}
