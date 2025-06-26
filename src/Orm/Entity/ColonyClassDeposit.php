<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\ColonyClassDepositRepository;

#[Table(name: 'stu_colony_class_deposit')]
#[Entity(repositoryClass: ColonyClassDepositRepository::class)]
class ColonyClassDeposit
{
    #[Column(type: 'integer')]
    private int $min_amount = 0;

    #[Column(type: 'integer')]
    private int $max_amount = 0;

    #[Id]
    #[ManyToOne(targetEntity: ColonyClass::class)]
    #[JoinColumn(name: 'colony_class_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ColonyClass $colonyClass;

    #[Id]
    #[ManyToOne(targetEntity: Commodity::class)]
    #[JoinColumn(name: 'commodity_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Commodity $commodity;

    public function getColonyClass(): ColonyClass
    {
        return $this->colonyClass;
    }

    public function getCommodity(): Commodity
    {
        return $this->commodity;
    }

    public function getMinAmount(): int
    {
        return $this->min_amount;
    }

    public function getMaxAmount(): int
    {
        return $this->max_amount;
    }
}
