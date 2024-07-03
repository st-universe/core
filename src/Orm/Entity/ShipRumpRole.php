<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Override;
use Stu\Orm\Repository\ShipRumpRoleRepository;

#[Table(name: 'stu_rumps_roles')]
#[Entity(repositoryClass: ShipRumpRoleRepository::class)]
class ShipRumpRole implements ShipRumpRoleInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'string')]
    private string $name = '';

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): ShipRumpRoleInterface
    {
        $this->name = $name;

        return $this;
    }
}
