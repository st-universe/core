<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\UserTutorialRepository;

#[Table(name: 'stu_user_tutorial')]
#[Entity(repositoryClass: UserTutorialRepository::class)]
class UserTutorial
{
    #[Id]
    #[ManyToOne(targetEntity: User::class, inversedBy: 'tutorials')]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    #[Id]
    #[ManyToOne(targetEntity: TutorialStep::class)]
    #[JoinColumn(name: 'tutorial_step_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private TutorialStep $tutorialStep;

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): UserTutorial
    {
        $this->user = $user;
        return $this;
    }

    public function getTutorialStep(): TutorialStep
    {
        return $this->tutorialStep;
    }

    public function setTutorialStep(TutorialStep $tutorialStep): UserTutorial
    {
        $this->tutorialStep = $tutorialStep;
        return $this;
    }
}
