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

#[Table(name: 'stu_research_dependencies')]
#[Entity(repositoryClass: 'Stu\Orm\Repository\ResearchDependencyRepository')]
class ResearchDependency implements ResearchDependencyInterface
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

    #[ManyToOne(targetEntity: 'Stu\Orm\Entity\Research')]
    #[JoinColumn(name: 'research_id', referencedColumnName: 'id')]
    private ResearchInterface $research;

    #[ManyToOne(targetEntity: 'Stu\Orm\Entity\Research')]
    #[JoinColumn(name: 'depends_on', referencedColumnName: 'id')]
    private ResearchInterface $research_depends_on;

    public function getId(): int
    {
        return $this->id;
    }

    public function getResearchId(): int
    {
        return $this->research_id;
    }

    public function setResearchId(int $researchId): ResearchDependencyInterface
    {
        $this->research_id = $researchId;

        return $this;
    }

    public function getDependsOn(): int
    {
        return $this->depends_on;
    }

    public function setDependsOn(int $dependsOn): ResearchDependencyInterface
    {
        $this->depends_on = $dependsOn;

        return $this;
    }

    public function getMode(): ResearchModeEnum
    {
        return $this->mode;
    }

    public function setMode(ResearchModeEnum $mode): ResearchDependencyInterface
    {
        $this->mode = $mode;

        return $this;
    }

    public function getResearch(): ResearchInterface
    {
        return $this->research;
    }

    public function getResearchDependOn(): ResearchInterface
    {
        return $this->research_depends_on;
    }
}
