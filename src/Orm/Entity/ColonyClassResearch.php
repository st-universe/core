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
     *
     * @var int
     */
    private $id;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $research_id;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $planet_type_id;

    /**
     *
     * @ManyToOne(targetEntity="Research")
     * @JoinColumn(name="research_id", referencedColumnName="id")
     */
    private ?ResearchInterface $research = null;

    /**
     *
     * @ManyToOne(targetEntity="ColonyClass")
     * @JoinColumn(name="planet_type_id", referencedColumnName="id")
     */
    private ?ColonyClassInterface $colonyClass = null;

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
