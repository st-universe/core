<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Component\Ship\System\ShipSystemTypeEnum;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ShipSystemRepository")
 * @Table(
 *     name="stu_ships_systems",
 *     indexes={
 *         @Index(name="ship_system_ship_idx", columns={"ships_id"}),
 *         @Index(name="ship_system_status_idx", columns={"status"}),
 *         @Index(name="ship_system_type_idx", columns={"system_type"}),
 *         @Index(name="ship_system_module_idx", columns={"module_id"})
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
    private $mode = 1;

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

    public function getName(): string
    {
        return ShipSystemTypeEnum::getDescription($this->getSystemType());
    }

    public function getCssClass(): string
    {
        if ($this->getStatus() < 1)
        {
            return _("sysStatus0");
        } elseif ($this->getStatus() < 26)
        {
            return _("sysStatus1to25");
        } elseif ($this->getStatus() < 51)
        {
            return _("sysStatus26to50");
        } elseif ($this->getStatus() < 76)
        {
            return _("sysStatus51to75");
        } else
        {
            return _("sysStatus76to100");
        }
    }

    public function getMode(): int
    {
        return $this->mode;
    }

    public function setMode(int $mode): ShipSystemInterface
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * @deprecated
     */
    public function isActivateable(): bool
    {
        return true;
    }

    public function isDisabled(): bool
    {
        return $this->getStatus() === 0
            || $this->getMode() < 2;
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
