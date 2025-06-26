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
use Stu\Component\Research\ResearchModeEnum;
use Stu\Orm\Repository\ResearchDependencyRepository;

#[Table(name: 'stu_research_dependencies')]
#[Entity(repositoryClass: ResearchDependencyRepository::class)]
class ResearchDependency
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $research_id;

    #[Column(type: 'integer')]
    private int $depends_on;

    #[Column(type: 'smallint', enumType: ResearchModeEnum::class)]
    private ResearchModeEnum $mode;

    #[ManyToOne(targetEntity: Research::class)]
    #[JoinColumn(name: 'research_id', nullable: false, referencedColumnName: 'id')]
    private Research $research;

    #[ManyToOne(targetEntity: Research::class)]
    #[JoinColumn(name: 'depends_on', nullable: false, referencedColumnName: 'id')]
    private Research $research_depends_on;

    public function getId(): int
    {
        return $this->id;
    }

    public function getResearchId(): int
    {
        return $this->research_id;
    }

    public function setResearchId(int $researchId): ResearchDependency
    {
        $this->research_id = $researchId;

        return $this;
    }

    public function getDependsOn(): int
    {
        return $this->depends_on;
    }

    public function setDependsOn(int $dependsOn): ResearchDependency
    {
        $this->depends_on = $dependsOn;

        return $this;
    }

    public function getMode(): ResearchModeEnum
    {
        return $this->mode;
    }

    public function setMode(ResearchModeEnum $mode): ResearchDependency
    {
        $this->mode = $mode;

        return $this;
    }

    public function getResearch(): Research
    {
        return $this->research;
    }

    public function getResearchDependOn(): Research
    {
        return $this->research_depends_on;
    }
}
