<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[Table(name: 'stu_planets_commodity')]
#[Index(name: 'planet_classes_idx', columns: ['planet_classes_id'])]
#[Index(name: 'planet_commodity_commodity_idx', columns: ['commodity_id'])]
#[Index(name: 'planet_commodity_count_idx', columns: ['count'])]
#[UniqueConstraint(name: 'planet_commodity_unique_idx', columns: ['planet_classes_id', 'commodity_id'])]
#[Entity]
class PlanetCommodity
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $planet_classes_id = 0;

    #[Column(type: 'integer')]
    private int $commodity_id = 0;

    #[Column(type: 'smallint')]
    private int $count = 0;
}
