<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;

#[Entity]
class TTrumfieldItem
{
    #[Id]
    #[Column(type: 'integer')]
    private int $id = 0;

    #[Column(type: 'integer')]
    private int $former_rump_id = 0;

    #[Column(type: 'integer')]
    private int $hull = 0;

    public function getId(): int
    {
        return $this->id;
    }

    public function getFormerRumpId(): int
    {
        return $this->former_rump_id;
    }

    public function getHull(): int
    {
        return $this->hull;
    }
}
