<?php

namespace Stu\Orm\Entity;

interface UserTutorialInterface
{
    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): UserTutorialInterface;

    public function getTutorialStep(): TutorialStepInterface;

    public function setTutorialStep(TutorialStepInterface $tutorialStep): UserTutorialInterface;
}
