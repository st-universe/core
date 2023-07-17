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
 * @Entity(repositoryClass="Stu\Orm\Repository\CrewTrainingRepository")
 * @Table(
 *     name="stu_crew_training",
 *     indexes={
 *         @Index(name="crew_training_colony_idx", columns={"colony_id"}),
 *         @Index(name="crew_training_user_idx", columns={"user_id"})
 *     }
 * )
 **/
class CrewTraining implements CrewTrainingInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     *
     */
    private int $id;

    /**
     * @Column(type="integer")
     *
     */
    private int $user_id = 0;

    /**
     * @Column(type="integer")
     *
     */
    private int $colony_id = 0;

    /**
     *
     * @ManyToOne(targetEntity="Colony")
     * @JoinColumn(name="colony_id", referencedColumnName="id")
     */
    private ColonyInterface $colony;

    /**
     *
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private UserInterface $user;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getColonyId(): int
    {
        return $this->colony_id;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): CrewTrainingInterface
    {
        $this->user = $user;
        return $this;
    }

    public function getColony(): ColonyInterface
    {
        return $this->colony;
    }

    public function setColony(ColonyInterface $colony): CrewTrainingInterface
    {
        $this->colony = $colony;
        return $this;
    }
}
