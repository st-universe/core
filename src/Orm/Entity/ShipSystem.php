<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Modules;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ShipSystemRepository")
 * @Table(
 *     name="stu_ships_systems",
 *     indexes={
 *         @Index(name="ship_idx", columns={"ships_id"})
 *     }
 * )
 **/
class ShipSystem implements ShipSystemInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="integer") * */
    private $ships_id = 0;

    /** @Column(type="smallint") * */
    private $system_type = 0;

    /** @Column(type="integer", nullable=true) * */
    private $module_id = 0;

    /** @Column(type="smallint") * */
    private $status = 0;

    public function getId(): int
    {
        return $this->id;
    }

    public function getShipId(): int
    {
        return $this->ships_id;
    }

    public function setShipId(int $shipId): ShipSystemInterface
    {
        $this->ships_id = $shipId;

        return $this;
    }

    public function getSystemType(): int
    {
        return $this->system_type;
    }

    public function setSystemType(int $systemType): ShipSystemInterface
    {
        $this->system_type = $systemType;

        return $this;
    }

    public function getModuleId(): int
    {
        return $this->module_id;
    }

    public function setModuleId(int $moduleId): ShipSystemInterface
    {
        $this->module_id = $moduleId;

        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): ShipSystemInterface
    {
        $this->status = $status;

        return $this;
    }

    public function isActivateable(): bool
    {
        // @todo
        return true;
    }

    public function getEnergyCosts(): int
    {
        // @todo
        return 1;
    }

    public function isDisabled(): bool
    {
        return $this->getStatus() === 0;
    }

    public function getModule(): Modules
    {
        return new Modules($this->getModuleId());
    }
}
