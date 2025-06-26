<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\ResearchedRepository;

#[Table(name: 'stu_researched')]
#[Entity(repositoryClass: ResearchedRepository::class)]
class Researched
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $research_id = 0;

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'integer')]
    private int $aktiv = 0;

    #[Column(type: 'integer')]
    private int $finished = 0;

    #[ManyToOne(targetEntity: Research::class)]
    #[JoinColumn(name: 'research_id', nullable: false, referencedColumnName: 'id')]
    private Research $research;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): Researched
    {
        $this->user = $user;
        return $this;
    }

    public function getActive(): int
    {
        return $this->aktiv;
    }

    public function setActive(int $active): Researched
    {
        $this->aktiv = $active;

        return $this;
    }

    public function getFinished(): int
    {
        return $this->finished;
    }

    public function setFinished(int $finished): Researched
    {
        $this->finished = $finished;

        return $this;
    }

    public function getResearch(): Research
    {
        return $this->research;
    }

    public function setResearch(Research $research): Researched
    {
        $this->research = $research;

        return $this;
    }

    public function getResearchId(): int
    {
        return $this->research_id;
    }

    public function getProgress(): int
    {
        return $this->getResearch()->getPoints() - $this->getActive();
    }
}
