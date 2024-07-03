<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Override;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_pirate_setup')]
#[Entity(repositoryClass: 'Stu\Orm\Repository\PirateSetupRepository')]
class PirateSetup implements PirateSetupInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'string', length: 200)]
    private string $name;

    #[Column(type: 'integer')]
    private int $probability_weight;

    /**
     * @var ArrayCollection<int, PirateSetupBuildplanInterface>
     */
    #[OneToMany(targetEntity: 'PirateSetupBuildplan', mappedBy: 'setup')]
    private Collection $setupBuildplans;

    public function __construct()
    {
        $this->setupBuildplans = new ArrayCollection();
    }

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
    public function getProbabilityWeight(): int
    {
        return $this->probability_weight;
    }

    #[Override]
    public function getSetupBuildplans(): Collection
    {
        return $this->setupBuildplans;
    }
}
