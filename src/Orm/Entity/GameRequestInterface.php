<?php

namespace Stu\Orm\Entity;

interface GameRequestInterface
{
    public function getId(): int;

    public function setUserId(?UserInterface $user): GameRequestInterface;

    public function setTurnId(GameTurnInterface $turn): GameRequestInterface;

    public function setTime(int $time): GameRequestInterface;

    public function setModule(string $module): GameRequestInterface;

    public function setAction(?string $action): GameRequestInterface;

    public function setActionMs(int $actionMs): GameRequestInterface;

    public function setView(?string $view): GameRequestInterface;

    public function setViewMs(int $viewMs): GameRequestInterface;

    public function setRenderMs(int $renderMs): GameRequestInterface;
}
