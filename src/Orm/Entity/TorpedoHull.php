<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\TorpedoHullRepository")
 * @Table(
 *     name="stu_torpedo_hull")
 **/
class TorpedoHull implements TorpedoHullInterface
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
    private int $module_id = 0;

    /**
     * @Column(type="integer")
     *
     */
    private int $torpedo_type = 0;

    /**
     * @Column(type="integer")
     *
     */
    private int $modificator = 0;


    /**
     * @var ?TorpedoType
     *
     * @ManyToOne(targetEntity="TorpedoType")
     * @JoinColumn(name="torpedo_type", referencedColumnName="id")
     */
    private $torpedo;

    /**
     * @var ?Module
     *
     * @ManyToOne(targetEntity="Module")
     * @JoinColumn(name="module_id", referencedColumnName="id")
     */
    private $module;

    public function getId(): int
    {
        return $this->id;
    }

    public function getModuleId(): int
    {
        return $this->module_id;
    }

    public function setModuleId(int $moduleId): TorpedoHullInterface
    {
        $this->module_id = $moduleId;

        return $this;
    }

    public function getTorpedoType(): int
    {
        return $this->torpedo_type;
    }

    public function setTorpedoType(int $torpedoType): TorpedoHullInterface
    {
        $this->torpedo_type = $torpedoType;

        return $this;
    }

    public function getModificator(): int
    {
        return $this->modificator;
    }

    public function setModificator(int $Modificator): TorpedoHullInterface
    {
        $this->modificator = $Modificator;

        return $this;
    }

    public function getTorpedo(): ?TorpedoTypeInterface
    {
        return $this->torpedo;
    }

    public function getModule(): ?ModuleInterface
    {
        return $this->module;
    }
}