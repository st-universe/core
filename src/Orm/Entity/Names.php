<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Override;
use Stu\Component\Game\NameTypeEnum;
use Stu\Orm\Repository\NamesRepository;

#[Table(name: 'stu_names')]
#[Entity(repositoryClass: NamesRepository::class)]
class Names implements NamesInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'string', length: 255)]
    private string $name;

    #[Column(type: 'integer', nullable: true)]
    private ?int $count = null;

    #[Column(type: 'integer', enumType: NameTypeEnum::class)]
    private NameTypeEnum $type;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[Override]
    public function getCount(): ?int
    {
        return $this->count;
    }

    #[Override]
    public function setCount(int $count): void
    {
        $this->count = $count;
    }

    #[Override]
    public function getType(): NameTypeEnum
    {
        return $this->type;
    }
}
