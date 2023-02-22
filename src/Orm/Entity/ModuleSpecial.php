<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Module\ShipModule\ModuleSpecialAbilityEnum;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ModuleSpecialRepository")
 * @Table(
 *     name="stu_modules_specials",
 *     indexes={
 *         @Index(name="module_special_module_idx", columns={"module_id"})
 *     }
 * )
 **/
class ModuleSpecial implements ModuleSpecialInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     *
     * @var int
     */
    private $id;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $module_id = 0;

    /**
     * @Column(type="smallint")
     *
     * @var int
     */
    private $special_id = 0;

    /**
     * @var ModuleInterface
     *
     * @ManyToOne(targetEntity="Module", inversedBy="moduleSpecials")
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

    public function setModuleId(int $moduleId): ModuleSpecialInterface
    {
        $this->module_id = $moduleId;

        return $this;
    }

    public function getSpecialId(): int
    {
        return $this->special_id;
    }

    public function setSpecialId(int $specialId): ModuleSpecialInterface
    {
        $this->special_id = $specialId;

        return $this;
    }

    public function getName(): string
    {
        return ModuleSpecialAbilityEnum::getDescription($this->getSpecialId());
    }
}
