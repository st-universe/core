<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\GameRequestRepository;
use Throwable;

/**
 *
 * @todo remove entity and repo
 **/
#[Table(name: 'stu_game_request')]
#[Index(name: 'game_request_idx', columns: ['user_id', 'action', 'view'])]
#[Entity(repositoryClass: GameRequestRepository::class)]
class GameRequest
{
    public const TABLE_NAME = 'stu_game_request';

    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer', nullable: true)]
    private ?int $user_id = null;

    #[Column(type: 'integer')]
    private int $turn_id = 0;

    #[Column(type: 'integer')]
    private int $time;

    #[Column(type: 'string', nullable: true)]
    private ?string $module = null;

    #[Column(type: 'string', nullable: true)]
    private ?string $action = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $action_ms = null;

    #[Column(type: 'string', nullable: true)]
    private ?string $view = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $view_ms = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $render_ms = null;

    #[Column(type: 'text', nullable: true)]
    private ?string $params = null;

    /** @var array<mixed> */
    private array $parameterArray = [];

    private string $requestId = '';

    /** @var array<Throwable> */
    private array $errors = [];

    public function getId(): int
    {
        return $this->id;
    }

    public function setUserId(?User $user): GameRequest
    {
        if ($user !== null) {
            $this->user_id = $user->getId();
        }
        return $this;
    }

    public function setTurnId(GameTurn $turn): GameRequest
    {
        $this->turn_id = $turn->getId();
        return $this;
    }

    public function setTime(int $time): GameRequest
    {
        $this->time = $time;
        return $this;
    }

    public function setModule(string $module): GameRequest
    {
        $this->module = $module;
        return $this;
    }

    public function setAction(string $action): GameRequest
    {
        $this->action = $action;
        $this->unsetParameter($action);

        return $this;
    }

    public function setActionMs(int $actionMs): GameRequest
    {
        $this->action_ms = $actionMs;
        return $this;
    }

    public function setView(string $view): GameRequest
    {
        $this->view = $view;
        $this->unsetParameter($view);

        return $this;
    }

    public function setViewMs(int $viewMs): GameRequest
    {
        $this->view_ms = $viewMs;
        return $this;
    }

    public function setRenderMs(int $renderMs): GameRequest
    {
        $this->render_ms = $renderMs;
        return $this;
    }

    /**
     * @param array<mixed> $parameter
     */
    public function setParameterArray(array $parameter): GameRequest
    {
        $this->params = (string) json_encode($parameter, JSON_PRETTY_PRINT);
        $this->parameterArray = $parameter;
        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function getParameterArray(): array
    {
        return $this->parameterArray;
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

    public function getModule(): ?string
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

    public function setRequestId(string $requestId): GameRequest
    {
        $this->requestId = $requestId;

        return $this;
    }

    public function addError(Throwable $error): GameRequest
    {
        $this->errors[] = $error;

        return $this;
    }

    /**
     * @return array<Throwable>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    private function unsetParameter(string $key): void
    {
        unset($this->parameterArray[$key]);
    }
}
