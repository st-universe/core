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
use Stu\Orm\Repository\LotteryWinnerBuildplanRepository;

#[Table(name: 'stu_lottery_buildplan')]
#[Entity(repositoryClass: LotteryWinnerBuildplanRepository::class)]
class LotteryWinnerBuildplan
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $buildplan_id;

    #[Column(type: 'integer')]
    private int $chance;

    #[Column(type: 'integer', nullable: true)]
    private ?int $faction_id = null;

    #[ManyToOne(targetEntity: SpacecraftBuildplan::class)]
    #[JoinColumn(name: 'buildplan_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private SpacecraftBuildplan $buildplan;

    public function getId(): int
    {
        return $this->id;
    }

    public function getBuildplan(): SpacecraftBuildplan
    {
        return $this->buildplan;
    }

    public function getChance(): int
    {
        return $this->chance;
    }

    public function getFactionId(): ?int
    {
        return $this->faction_id;
    }
}
