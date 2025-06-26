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
use Stu\Orm\Repository\ColonyClassResearchRepository;

//TODO rename table and column planet_type_id
#[Table(name: 'stu_planet_type_research')]
#[Entity(repositoryClass: ColonyClassResearchRepository::class)]
class ColonyClassResearch
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ManyToOne(targetEntity: Research::class)]
    #[JoinColumn(name: 'research_id', nullable: false, referencedColumnName: 'id')]
    private Research $research;

    #[ManyToOne(targetEntity: ColonyClass::class)]
    #[JoinColumn(name: 'planet_type_id', nullable: false, referencedColumnName: 'id')]
    private ColonyClass $colonyClass;

    public function getId(): int
    {
        return $this->id;
    }

    public function getResearch(): Research
    {
        return $this->research;
    }

    public function setResearch(Research $research): ColonyClassResearch
    {
        $this->research = $research;
        return $this;
    }

    public function getColonyClass(): ColonyClass
    {
        return $this->colonyClass;
    }

    public function setColonyClass(ColonyClass $colonyClass): ColonyClassResearch
    {
        $this->colonyClass = $colonyClass;
        return $this;
    }
}
