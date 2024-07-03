<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Override;
use Stu\Orm\Repository\ColonyClassResearchRepository;

//TODO rename table and column planet_type_id
#[Table(name: 'stu_planet_type_research')]
#[Index(name: 'planet_type_idx', columns: ['planet_type_id'])]
#[Entity(repositoryClass: ColonyClassResearchRepository::class)]
class ColonyClassResearch implements ColonyClassResearchInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $research_id;

    #[Column(type: 'integer')]
    private int $planet_type_id;

    #[ManyToOne(targetEntity: 'Research')]
    #[JoinColumn(name: 'research_id', referencedColumnName: 'id')]
    private ResearchInterface $research;

    #[ManyToOne(targetEntity: 'ColonyClass')]
    #[JoinColumn(name: 'planet_type_id', referencedColumnName: 'id')]
    private ColonyClassInterface $colonyClass;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getResearch(): ResearchInterface
    {
        return $this->research;
    }

    #[Override]
    public function setResearch(ResearchInterface $research): ColonyClassResearchInterface
    {
        $this->research = $research;
        return $this;
    }

    #[Override]
    public function getColonyClass(): ColonyClassInterface
    {
        return $this->colonyClass;
    }

    #[Override]
    public function setColonyClass(ColonyClassInterface $colonyClass): ColonyClassResearchInterface
    {
        $this->colonyClass = $colonyClass;
        return $this;
    }
}
