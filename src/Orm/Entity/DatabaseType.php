<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\DatabaseTypeRepository;

#[Table(name: 'stu_database_types')]
#[Entity(repositoryClass: DatabaseTypeRepository::class)]
class DatabaseType
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

    public function setDescription(string $description): DatabaseType
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setMacro(string $macro): DatabaseType
    {
        $this->macro = $macro;

        return $this;
    }

    public function getMacro(): string
    {
        return $this->macro;
    }
}
