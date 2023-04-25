<?php

namespace Stu\Orm\Entity;

use Throwable;

interface GameRequestInterface
{
    public function getId(): int;

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
    public function setParameterArray(array $parameter): GameRequestInterface;

    /**
     * @return array<mixed>
     */
    public function getParameterArray(): array;


    public function getUserId(): ?int;

    public function getTurnId(): int;

    public function getTime(): int;

    public function getModule(): ?string;

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
