<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Override;
use Stu\Orm\Repository\LotteryWinnerBuildplanRepository;

#[Table(name: 'stu_lottery_buildplan')]
#[Entity(repositoryClass: LotteryWinnerBuildplanRepository::class)]
class LotteryWinnerBuildplan implements LotteryWinnerBuildplanInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $buildplan_id;

    #[Column(type: 'integer')]
    private int $chance;

    #[ManyToOne(targetEntity: 'ShipBuildplan')]
    #[JoinColumn(name: 'buildplan_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ShipBuildplanInterface $buildplan;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getBuildplan(): ShipBuildplanInterface
    {
        return $this->buildplan;
    }

    #[Override]
    public function getChance(): int
    {
        return $this->chance;
    }
}
