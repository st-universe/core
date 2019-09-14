<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use User;

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

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $userId): AllianceJobInterface
    {
        $this->user_id = $userId;

        return $this;
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

    public function getUser(): User
    {
        return new User($this->getUserId());
    }
}
