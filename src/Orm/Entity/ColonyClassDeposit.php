<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Orm\Repository\ColonyClassDepositRepository;
use Override;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_colony_class_deposit')]
#[Entity(repositoryClass: ColonyClassDepositRepository::class)]
class ColonyClassDeposit implements ColonyClassDepositInterface
{
    #[Id]
    #[Column(type: 'integer')]
    private int $colony_class_id;

    #[Id]
    #[Column(type: 'integer')]
    private int $commodity_id;

    #[Column(type: 'integer')]
    private int $min_amount = 0;

    #[Column(type: 'integer')]
    private int $max_amount = 0;

    #[ManyToOne(targetEntity: 'ColonyClass')]
    #[JoinColumn(name: 'colony_class_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ColonyClassInterface $colonyClass;

    #[ManyToOne(targetEntity: 'Commodity')]
    #[JoinColumn(name: 'commodity_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private CommodityInterface $commodity;

    #[Override]
    public function getColonyClass(): ColonyClassInterface
    {
        return $this->colonyClass;
    }

    #[Override]
    public function getCommodity(): CommodityInterface
    {
        return $this->commodity;
    }

    #[Override]
    public function getMinAmount(): int
    {
        return $this->min_amount;
    }

    #[Override]
    public function getMaxAmount(): int
    {
        return $this->max_amount;
    }
}
