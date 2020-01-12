<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\PlanetTypeResearchRepository")
 * @Table(
 *     name="stu_planet_type_research",
 *     indexes={
 *         @Index(name="planet_type_idx", columns={"planet_type_id"})
 *     }
 * )
 **/
class PlanetTypeResearch implements PlanetTypeResearchInterface {
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
     * @ManyToOne(targetEntity="PlanetType")
     * @JoinColumn(name="planet_type_id", referencedColumnName="id")
     */
    private $planetType;

    public function getId(): int
    {
        return $this->id;
    }

    public function getResearch(): ResearchInterface
    {
        return $this->research;
    }

    public function setResearch(ResearchInterface $research): PlanetTypeResearchInterface
    {
        $this->research = $research;
        return $this;
    }

    public function getPlanetType(): PlanetTypeInterface
    {
        return $this->planetType;
    }

    public function setPlanetType(PlanetTypeInterface $planetType): PlanetTypeResearchInterface
    {
        $this->planetType = $planetType;
        return $this;
    }
}
