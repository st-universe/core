<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Override;
use Stu\Orm\Repository\UserTutorialRepository;

#[Table(name: 'stu_user_tutorial')]
#[Entity(repositoryClass: UserTutorialRepository::class)]
class UserTutorial implements UserTutorialInterface
{
    #[Id]
    #[ManyToOne(targetEntity: 'User', inversedBy: 'tutorials')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

    #[Id]
    #[ManyToOne(targetEntity: 'TutorialStep')]
    #[JoinColumn(name: 'tutorial_step_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private TutorialStepInterface $tutorialStep;

    #[Override]
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    #[Override]
    public function setUser(UserInterface $user): UserTutorialInterface
    {
        $this->user = $user;
        return $this;
    }

    #[Override]
    public function getTutorialStep(): TutorialStepInterface
    {
        return $this->tutorialStep;
    }

    #[Override]
    public function setTutorialStep(TutorialStepInterface $tutorialStep): UserTutorialInterface
    {
        $this->tutorialStep = $tutorialStep;
        return $this;
    }
}
