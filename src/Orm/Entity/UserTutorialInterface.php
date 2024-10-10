<?php

namespace Stu\Orm\Entity;

interface UserTutorialInterface
{
    public function getId(): int;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): UserTutorialInterface;

    public function getModule(): string;

    public function setModule(string $module): UserTutorialInterface;

    public function getStep(): int;

    public function setStep(int $step): UserTutorialInterface;
}