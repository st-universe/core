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
use Override;
use Stu\Component\Spacecraft\ModuleSpecialAbilityEnum;
use Stu\Orm\Repository\ModuleSpecialRepository;

#[Table(name: 'stu_modules_specials')]
#[Index(name: 'module_special_module_idx', columns: ['module_id'])]
#[Entity(repositoryClass: ModuleSpecialRepository::class)]
class ModuleSpecial implements ModuleSpecialInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $module_id = 0;

    #[Column(type: 'smallint', enumType: ModuleSpecialAbilityEnum::class)]
    private ModuleSpecialAbilityEnum $special_id = ModuleSpecialAbilityEnum::RPG;

    #[ManyToOne(targetEntity: Module::class, inversedBy: 'moduleSpecials')]
    #[JoinColumn(name: 'module_id', nullable: false, referencedColumnName: 'id')]
    private ModuleInterface $module;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getModuleId(): int
    {
        return $this->module_id;
    }

    #[Override]
    public function setModuleId(int $moduleId): ModuleSpecialInterface
    {
        $this->module_id = $moduleId;

        return $this;
    }

    #[Override]
    public function getSpecialId(): ModuleSpecialAbilityEnum
    {
        return $this->special_id;
    }

    #[Override]
    public function setSpecialId(ModuleSpecialAbilityEnum $specialId): ModuleSpecialInterface
    {
        $this->special_id = $specialId;

        return $this;
    }

    #[Override]
    public function getName(): string
    {
        return $this->getSpecialId()->getDescription();
    }
}
