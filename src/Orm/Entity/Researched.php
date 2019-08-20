<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use UserData;

/**
 * @Entity
 * @Table(name="stu_researched")
 * @Entity(repositoryClass="Stu\Orm\Repository\ResearchedRepository")
 **/
class Researched implements ResearchedInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="integer") * */
    private $research_id;

    /** @Column(type="integer") * */
    private $user_id;

    /** @Column(type="integer") * */
    private $aktiv;

    /** @Column(type="integer") * */
    private $finished;

    /**
     * @ManyToOne(targetEntity="Stu\Orm\Entity\Research")
     * @JoinColumn(name="research_id", referencedColumnName="id")
     */
    private $research;

    public function getId(): int
    {
        return $this->id;
    }

    public function getResearchId(): int
    {
        return $this->research_id;
    }

    public function setResearchId(int $reserchId): ResearchedInterface
    {
        $this->research_id = $reserchId;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $userId): ResearchedInterface
    {
        $this->user_id = $userId;

        return $this;
    }

    public function getActive(): int
    {
        return $this->aktiv;
    }

    public function setActive(int $active): ResearchedInterface
    {
        $this->aktiv = $active;

        return $this;
    }

    public function getFinished(): int
    {
        return $this->finished;
    }

    public function setFinished(int $finished): ResearchedInterface
    {
        $this->finished = $finished;

        return $this;
    }

    public function getResearch(): ResearchInterface {
        return $this->research;
    }

    public function isResearchInProgress(): bool {
        return $this->getActive() > 0;
    }

    public function isResearchFinished(): bool {
        return $this->getFinished() > 0;
    }

    public function getUser(): UserData {
        return ResourceCache()->getObject('user', $this->getUserId());
    }
}
