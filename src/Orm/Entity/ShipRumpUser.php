<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ShipRumpUserRepository")
 * @Table(
 *     name="stu_rumps_user",
 *     indexes={
 *         @Index(name="rump_user_idx", columns={"rump_id", "user_id"})
 *     }
 * )
 **/
class ShipRumpUser implements ShipRumpUserInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="integer") * */
    private $rump_id = 0;

    /** @Column(type="integer") * */
    private $user_id = 0;

    public function getId(): int
    {
        return $this->id;
    }

    public function getShipRumpId(): int
    {
        return $this->rump_id;
    }

    public function setShipRumpId(int $shipRumpId): ShipRumpUserInterface
    {
        $this->rump_id = $shipRumpId;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $userId): ShipRumpUserInterface
    {
        $this->user_id = $userId;

        return $this;
    }
}
