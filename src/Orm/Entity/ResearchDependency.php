<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ResearchDependencyRepository")
 * @Table(name="stu_research_dependencies")
 **/
class ResearchDependency implements ResearchDependencyInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="integer") * */
    private $research_id;

    /** @Column(type="integer") * */
    private $depends_on;

    /** @Column(type="smallint") * */
    private $mode;

    /** @Column(type="boolean") */
    private $is_award_dependency = false;

    /**
     * @ManyToOne(targetEntity="Stu\Orm\Entity\Research")
     * @JoinColumn(name="research_id", referencedColumnName="id")
     */
    private $research;

    /**
     * @ManyToOne(targetEntity="Stu\Orm\Entity\Research")
     * @JoinColumn(name="depends_on", referencedColumnName="id")
     */
    private $research_depends_on;

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

    public function getMode(): int
    {
        return $this->mode;
    }

    public function setMode(int $mode): ResearchDependencyInterface
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
