<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Attribute\TruncateOnGameReset;
use Stu\Orm\Repository\UserMapRepository;

#[Table(name: 'stu_user_map')]
#[Entity(repositoryClass: UserMapRepository::class)]
#[TruncateOnGameReset]
class UserMap
{
    #[Id]
    #[Column(type: 'integer')]
    private int $cx = 0;

    #[Id]
    #[Column(type: 'integer')]
    private int $cy = 0;

    #[Column(type: 'integer')]
    private int $map_id = 0;

    #[Id]
    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    #[Id]
    #[ManyToOne(targetEntity: Layer::class)]
    #[JoinColumn(name: 'layer_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Layer $layer;
}
