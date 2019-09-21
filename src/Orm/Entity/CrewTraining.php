<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use User;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\CrewTrainingRepository")
 * @Table(
 *     name="stu_crew_training",
 *     indexes={
 *         @Index(name="colony_idx", columns={"colony_id"}),
 *         @Index(name="user_idx", columns={"user_id"})
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
     * @JoinColumn(name="colony_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $colony;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $userId): CrewTrainingInterface
    {
        $this->user_id = $userId;

        return $this;
    }

    public function getColonyId(): int
    {
        return $this->colony_id;
    }

    public function getUser(): User
    {
        return new User($this->getUserId());
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
