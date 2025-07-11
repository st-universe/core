<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_pirate_setup_buildplan')]
#[Entity()]
class PirateSetupBuildplan
{
    #[Column(type: 'integer')]
    private int $amount;

    #[Id]
    #[ManyToOne(targetEntity: PirateSetup::class, inversedBy: 'setupBuildplans')]
    #[JoinColumn(name: 'pirate_setup_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private PirateSetup $setup;

    #[Id]
    #[ManyToOne(targetEntity: SpacecraftBuildplan::class)]
    #[JoinColumn(name: 'buildplan_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private SpacecraftBuildplan $buildplan;

    public function getBuildplan(): SpacecraftBuildplan
    {
        return $this->buildplan;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }
}
