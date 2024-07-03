<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Orm\Repository\GameConfigRepository;
use Override;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_game_config')]
#[Index(name: 'option_idx', columns: ['option'])]
#[Entity(repositoryClass: GameConfigRepository::class)]
class GameConfig implements GameConfigInterface
{
    public const TABLE_NAME = 'stu_game_config';

    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'smallint')]
    private int $option = 0;

    #[Column(type: 'smallint')]
    private int $value = 0;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getOption(): int
    {
        return $this->option;
    }

    #[Override]
    public function setOption(int $option): GameConfigInterface
    {
        $this->option = $option;

        return $this;
    }

    #[Override]
    public function getValue(): int
    {
        return $this->value;
    }

    #[Override]
    public function setValue(int $value): GameConfigInterface
    {
        $this->value = $value;

        return $this;
    }
}
