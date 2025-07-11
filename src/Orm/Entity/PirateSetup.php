<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\PirateSetupRepository;

#[Table(name: 'stu_pirate_setup')]
#[Entity(repositoryClass: PirateSetupRepository::class)]
class PirateSetup
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
     * @var ArrayCollection<int, PirateSetupBuildplan>
     */
    #[OneToMany(targetEntity: PirateSetupBuildplan::class, mappedBy: 'setup')]
    private Collection $setupBuildplans;

    public function __construct()
    {
        $this->setupBuildplans = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getProbabilityWeight(): int
    {
        return $this->probability_weight;
    }

    /**
     * @return Collection<int, PirateSetupBuildplan>
     */
    public function getSetupBuildplans(): Collection
    {
        return $this->setupBuildplans;
    }
}
