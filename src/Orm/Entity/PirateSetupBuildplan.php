<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Override;

#[Table(name: 'stu_pirate_setup_buildplan')]
#[Entity()]
class PirateSetupBuildplan implements PirateSetupBuildplanInterface
{
    #[Column(type: 'integer')]
    private int $amount;

    #[Id]
    #[ManyToOne(targetEntity: 'PirateSetup', inversedBy: 'setupBuildplans')]
    #[JoinColumn(name: 'pirate_setup_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private PirateSetupInterface $setup;

    #[Id]
    #[ManyToOne(targetEntity: SpacecraftBuildplan::class)]
    #[JoinColumn(name: 'buildplan_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private SpacecraftBuildplanInterface $buildplan;

    #[Override]
    public function getBuildplan(): SpacecraftBuildplanInterface
    {
        return $this->buildplan;
    }

    #[Override]
    public function getAmount(): int
    {
        return $this->amount;
    }
}
