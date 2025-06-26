<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Stu\Component\Spacecraft\SpacecraftRumpRoleEnum;
use Stu\Orm\Repository\ShipRumpRoleRepository;

#[Table(name: 'stu_rumps_roles')]
#[Entity(repositoryClass: ShipRumpRoleRepository::class)]
class ShipRumpRole
{
    #[Id]
    #[GeneratedValue(strategy: 'IDENTITY')]
    #[Column(type: 'integer', enumType: SpacecraftRumpRoleEnum::class)]
    private SpacecraftRumpRoleEnum $id;

    #[Column(type: 'string')]
    private string $name = '';

    public function getId(): SpacecraftRumpRoleEnum
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): ShipRumpRole
    {
        $this->name = $name;

        return $this;
    }
}
