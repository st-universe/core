<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

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
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="integer") * */
    private $user_id = 0;

    /** @Column(type="integer") */
    private $colony_id = 0;

    /**
     * @ManyToOne(targetEntity="Colony")
     * @JoinColumn(name="colony_id", referencedColumnName="id")
     */
    private $colony;

    /**
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
