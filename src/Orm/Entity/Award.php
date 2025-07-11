<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\AwardRepository;

#[Table(name: 'stu_award')]
#[Entity(repositoryClass: AwardRepository::class)]
class Award
{
    #[Id]
    #[Column(type: 'integer')]
    private int $id;

    #[Column(type: 'integer')]
    private int $prestige = 0;

    #[Column(type: 'text')]
    private string $description = '';

    public function getId(): int
    {
        return $this->id;
    }

    public function getPrestige(): int
    {
        return $this->prestige;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
