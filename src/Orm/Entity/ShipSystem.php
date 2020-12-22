<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ShipSystemRepository")
 * @Table(
 *     name="stu_ships_systems",
 *     indexes={
 *         @Index(name="ship_system_ship_idx", columns={"ships_id"})
 *     }
 * )
 **/
class ShipSystem implements ShipSystemInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="integer") * */
    private $ships_id = 0;

    /** @Column(type="smallint") * */
    private $system_type = 0;

    /** @Column(type="integer", nullable=true) * */
    private $module_id = 0;

    /** @Column(type="smallint") * */
    private $status = 0;

    /** @Column(type="smallint") * */
    private $system_mode = 1;

    /**
     * @ManyToOne(targetEntity="Module")
     * @JoinColumn(name="module_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $module;

    /**
     * @ManyToOne(targetEntity="Ship")
     * @JoinColumn(name="ships_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $ship;

    public function getId(): int
    {
        return $this->id;
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

    public function getMode(): int
    {
        return $this->system_mode;
    }

    public function setMode(int $mode): ShipSystemInterface
    {
        $this->system_mode = $mode;

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

    public function getModule(): ModuleInterface
    {
        return $this->module;
    }

    public function setModule(ModuleInterface $module): ShipSystemInterface
    {
        $this->module = $module;

        return $this;
    }

    public function getShip(): ShipInterface
    {
        return $this->ship;
    }

    public function setShip(ShipInterface $ship): ShipSystemInterface
    {
        $this->ship = $ship;
        return $this;
    }
}
