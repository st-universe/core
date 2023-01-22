<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(
 *     name="stu_rumps_module_special",
 *     indexes={
 *         @Index(name="rump_module_special_ship_rump_idx", columns={"rump_id"})
 *     }
 * )
 **/
class ShipRumpModuleSpecial implements ShipRumpModuleSpecialInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="integer") * */
    private $rump_id = 0;

    /** @Column(type="integer") * */
    private $module_special_id = 0;

    public function getId(): int
    {
        return $this->id;
    }

    public function getShipRumpId(): int
    {
        return $this->rump_id;
    }

    public function setShipRumpId(int $shipRumpId): ShipRumpModuleSpecialInterface
    {
        $this->rump_id = $shipRumpId;

        return $this;
    }

    public function getModuleSpecialId(): int
    {
        return $this->module_special_id;
    }

    public function setModuleSpecialId(int $moduleSpecialId): ShipRumpModuleSpecialInterface
    {
        $this->module_special_id = $moduleSpecialId;

        return $this;
    }
}
