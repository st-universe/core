<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Orm\Repository\ResearchRepositoryInterface;

/**
 * @Entity
 * @Table(name="stu_research_dependencies")
 * @Entity(repositoryClass="Stu\Orm\Repository\ResearchDependencyRepository")
 **/
final class ResearchDependency implements ResearchDependencyInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="integer") * */
    private $research_id;

    /** @Column(type="integer") * */
    private $depends_on;

    /** @Column(type="smallint") * */
    private $mode;

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
        // @todo refactor
        global $container;

        return $container->get(ResearchRepositoryInterface::class)->find((int)$this->getResearchId());
    }

    public function getResearchDependOn(): ResearchInterface
    {
        // @todo refactor
        global $container;

        return $container->get(ResearchRepositoryInterface::class)->find((int)$this->getDependsOn());
    }
}
