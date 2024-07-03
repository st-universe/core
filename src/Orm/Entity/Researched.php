<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Orm\Repository\ResearchedRepository;
use Override;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_researched')]
#[Entity(repositoryClass: ResearchedRepository::class)]
class Researched implements ResearchedInterface
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
    #[JoinColumn(name: 'research_id', referencedColumnName: 'id')]
    private ResearchInterface $research;

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getUserId(): int
    {
        return $this->user_id;
    }

    #[Override]
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    #[Override]
    public function setUser(UserInterface $user): ResearchedInterface
    {
        $this->user = $user;
        return $this;
    }

    #[Override]
    public function getActive(): int
    {
        return $this->aktiv;
    }

    #[Override]
    public function setActive(int $active): ResearchedInterface
    {
        $this->aktiv = $active;

        return $this;
    }

    #[Override]
    public function getFinished(): int
    {
        return $this->finished;
    }

    #[Override]
    public function setFinished(int $finished): ResearchedInterface
    {
        $this->finished = $finished;

        return $this;
    }

    #[Override]
    public function getResearch(): ResearchInterface
    {
        return $this->research;
    }

    #[Override]
    public function setResearch(ResearchInterface $research): ResearchedInterface
    {
        $this->research = $research;

        return $this;
    }

    #[Override]
    public function getResearchId(): int
    {
        return $this->research_id;
    }

    #[Override]
    public function getProgress(): int
    {
        return $this->getResearch()->getPoints() - $this->getActive();
    }
}
