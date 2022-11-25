<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ColonyClassResearchRepository")
 * @Table(
 *     name="stu_planet_type_research",
 *     indexes={
 *         @Index(name="planet_type_idx", columns={"planet_type_id"})
 *     }
 * )
 **/
//TODO rename table and column planet_type_id
class ColonyClassResearch implements ColonyClassResearchInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="integer") */
    private $research_id;

    /** @Column(type="integer") */
    private $planet_type_id;

    /**
     * @ManyToOne(targetEntity="Research")
     * @JoinColumn(name="research_id", referencedColumnName="id")
     */
    private $research;

    /**
     * @ManyToOne(targetEntity="ColonyClass")
     * @JoinColumn(name="planet_type_id", referencedColumnName="id")
     */
    private $colonyClass;

    public function getId(): int
    {
        return $this->id;
    }

    public function getResearch(): ResearchInterface
    {
        return $this->research;
    }

    public function setResearch(ResearchInterface $research): ColonyClassResearchInterface
    {
        $this->research = $research;
        return $this;
    }

    public function getColonyClass(): ColonyClassInterface
    {
        return $this->colonyClass;
    }

    public function setColonyClass(ColonyClassInterface $colonyClass): ColonyClassResearchInterface
    {
        $this->colonyClass = $colonyClass;
        return $this;
    }
}
