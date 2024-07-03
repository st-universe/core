<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Override;
use Stu\Orm\Repository\AwardRepository;

#[Table(name: 'stu_award')]
#[Entity(repositoryClass: AwardRepository::class)]
class Award implements AwardInterface
{
    #[Id]
    #[Column(type: 'integer')]
    private int $id;

    #[Column(type: 'integer')]
    private int $prestige = 0;

    #[Column(type: 'text')]
    private string $description = '';

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getPrestige(): int
    {
        return $this->prestige;
    }

    #[Override]
    public function getDescription(): string
    {
        return $this->description;
    }
}
