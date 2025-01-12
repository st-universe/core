<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Override;
use Stu\Orm\Repository\DatabaseTypeRepository;

#[Table(name: 'stu_database_types')]
#[Entity(repositoryClass: DatabaseTypeRepository::class)]
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

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function setDescription(string $description): DatabaseTypeInterface
    {
        $this->description = $description;

        return $this;
    }

    #[Override]
    public function getDescription(): string
    {
        return $this->description;
    }

    #[Override]
    public function setMacro(string $macro): DatabaseTypeInterface
    {
        $this->macro = $macro;

        return $this;
    }

    #[Override]
    public function getMacro(): string
    {
        return $this->macro;
    }
}
