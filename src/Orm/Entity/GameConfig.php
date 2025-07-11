<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\GameConfigRepository;

#[Table(name: 'stu_game_config')]
#[Index(name: 'option_idx', columns: ['option'])]
#[Entity(repositoryClass: GameConfigRepository::class)]
class GameConfig
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

    public function getId(): int
    {
        return $this->id;
    }

    public function getOption(): int
    {
        return $this->option;
    }

    public function setOption(int $option): GameConfig
    {
        $this->option = $option;

        return $this;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(int $value): GameConfig
    {
        $this->value = $value;

        return $this;
    }
}
