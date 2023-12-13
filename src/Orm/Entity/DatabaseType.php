<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_database_types', options: ['engine' => 'InnoDB'])]
#[Entity(repositoryClass: 'Stu\Orm\Repository\DatabaseTypeRepository')]
class DatabaseType implements DatabaseTypeInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'string')]
    private string $description;

    #[Column(type: 'string')]
    private string $macro;

    public function getId(): int
    {
        return $this->id;
    }

    public function setDescription(string $description): DatabaseTypeInterface
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setMacro(string $macro): DatabaseTypeInterface
    {
        $this->macro = $macro;

        return $this;
    }

    public function getMacro(): string
    {
        return $this->macro;
    }
}
