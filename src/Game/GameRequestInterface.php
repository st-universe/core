<?php

namespace Stu\Game;

use Stu\Orm\Entity\GameTurnInterface;
use Stu\Orm\Entity\UserInterface;
use Throwable;

interface GameRequestInterface
{
    public function setUserId(?UserInterface $user): GameRequestInterface;

    public function setTurnId(GameTurnInterface $turn): GameRequestInterface;

    public function setTime(int $time): GameRequestInterface;

    public function setModule(string $module): GameRequestInterface;

    public function setAction(string $action): GameRequestInterface;

    public function setActionMs(int $actionMs): GameRequestInterface;

    public function setView(string $view): GameRequestInterface;

    public function setViewMs(int $viewMs): GameRequestInterface;

    public function setRenderMs(int $renderMs): GameRequestInterface;

    /**
     * @param array<mixed> $parameter
     */
    public function setParameter(array $parameter): GameRequestInterface;

    /**
     * @return array<mixed>
     */
    public function getParameter(): array;


    public function getUserId(): ?int;

    public function getTurnId(): int;

    public function getTime(): int;

    public function getModule(): string;

    public function getAction(): ?string;

    public function getActionMs(): ?int;

    public function getView(): ?string;

    public function getViewMs(): ?int;

    public function getRenderMs(): ?int;

    public function getRequestId(): string;

    public function setRequestId(string $requestId): GameRequestInterface;

    public function addError(Throwable $error): GameRequestInterface;

    /**
     * @return array<Throwable>
     */
    public function getErrors(): array;
}
