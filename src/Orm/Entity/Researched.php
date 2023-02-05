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

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ResearchedRepository")
 * @Table(name="stu_researched")
 **/
class Researched implements ResearchedInterface
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
    private $research_id = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $user_id = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $aktiv = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $finished = 0;

    /**
     * @var ResearchInterface
     *
     * @ManyToOne(targetEntity="Stu\Orm\Entity\Research")
     * @JoinColumn(name="research_id", referencedColumnName="id")
     */
    private $research;

    /**
     * @var UserInterface
     *
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): ResearchedInterface
    {
        $this->user = $user;
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

    public function getResearch(): ResearchInterface
    {
        return $this->research;
    }

    public function setResearch(ResearchInterface $research): ResearchedInterface
    {
        $this->research = $research;

        return $this;
    }

    public function getResearchId(): int
    {
        return $this->research_id;
    }

    public function getProgress(): int
    {
        return $this->getResearch()->getPoints() - $this->getActive();
    }
}
