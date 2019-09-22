<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\AllianceJobRepository")
 * @Table(
 *     name="stu_alliances_jobs",
 *     indexes={
 *     }
 * )
 **/
class AllianceJob implements AllianceJobInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="integer") * */
    private $alliance_id = 0;

    /** @Column(type="integer") * */
    private $user_id = 0;

    /** @Column(type="smallint") * */
    private $type = 0;

    /**
     * @ManyToOne(targetEntity="Alliance")
     * @JoinColumn(name="alliance_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $alliance;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id")
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

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): AllianceJobInterface
    {
        $this->type = $type;

        return $this;
    }

    public function getAlliance(): AllianceInterface
    {
        return $this->alliance;
    }

    public function setAlliance(AllianceInterface $alliance): AllianceJobInterface
    {
        $this->alliance = $alliance;

        return $this;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): AllianceJobInterface
    {
        $this->user = $user;
        return $this;
    }
}
