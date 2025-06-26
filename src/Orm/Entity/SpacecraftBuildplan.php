<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Orm\Repository\SpacecraftBuildplanRepository;

#[Table(name: 'stu_buildplan')]
#[Entity(repositoryClass: SpacecraftBuildplanRepository::class)]
#[UniqueConstraint(name: 'buildplan_signatures_idx', columns: ['user_id', 'rump_id', 'signature'])]
class SpacecraftBuildplan
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $rump_id = 0;

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'string')]
    private string $name = '';

    #[Column(type: 'integer')]
    private int $buildtime = 0;

    #[Column(type: 'string', length: 32, nullable: true)]
    private ?string $signature = '';

    #[Column(type: 'smallint')]
    private int $crew = 0;

    /**
     * @var ArrayCollection<int, Spacecraft>
     */
    #[OneToMany(targetEntity: Spacecraft::class, mappedBy: 'buildplan', fetch: 'EXTRA_LAZY')]
    private Collection $spacecrafts;

    #[ManyToOne(targetEntity: SpacecraftRump::class)]
    #[JoinColumn(name: 'rump_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private SpacecraftRump $shipRump;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    /**
     * @var ArrayCollection<int, BuildplanModule>
     */
    #[OneToMany(targetEntity: BuildplanModule::class, mappedBy: 'buildplan', indexBy: 'module_id', fetch: 'EXTRA_LAZY')]
    #[OrderBy(['module_id' => 'ASC'])]
    private Collection $modules;

    public function __construct()
    {
        $this->spacecrafts = new ArrayCollection();
        $this->modules = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getRumpId(): int
    {
        return $this->rump_id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): SpacecraftBuildplan
    {
        $this->user = $user;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): SpacecraftBuildplan
    {
        $this->name = $name;

        return $this;
    }

    public function getBuildtime(): int
    {
        return $this->buildtime;
    }

    public function setBuildtime(int $buildtime): SpacecraftBuildplan
    {
        $this->buildtime = $buildtime;

        return $this;
    }

    public function getSpacecraftCount(): int
    {
        return $this->getSpacecraftList()->count();
    }

    public function getSignature(): ?string
    {
        return $this->signature;
    }

    public function setSignature(?string $signature): SpacecraftBuildplan
    {
        $this->signature = $signature;

        return $this;
    }

    public function getCrew(): int
    {
        return $this->crew;
    }

    public function setCrew(int $crew): SpacecraftBuildplan
    {
        $this->crew = $crew;

        return $this;
    }

    /**
     * @return Collection<int, Spacecraft>
     */
    public function getSpacecraftList(): Collection
    {
        return $this->spacecrafts;
    }

    public function getRump(): SpacecraftRump
    {
        return $this->shipRump;
    }

    public function setRump(SpacecraftRump $shipRump): SpacecraftBuildplan
    {
        $this->shipRump = $shipRump;

        return $this;
    }

    /**
     * @return Collection<int, Module>
     */
    public function getModulesByType(SpacecraftModuleTypeEnum $type): Collection
    {
        return $this
            ->getModules()
            ->filter(fn(BuildplanModule $buildplanModule): bool => $buildplanModule->getModuleType() === $type)
            ->map(fn(BuildplanModule $buildplanModule): Module => $buildplanModule->getModule());
    }

    /**
     * @return Collection<int, BuildplanModule>
     */
    public function getModules(): Collection
    {
        return $this->modules;
    }

    /**
     * @return Collection<int, BuildplanModule>
     */
    public function getModulesOrdered(): Collection
    {
        $array = $this->modules->toArray();

        uasort($array, function (BuildplanModule $a, BuildplanModule $b): int {
            return $a->getModuleType()->getOrder() <=> $b->getModuleType()->getOrder();
        });

        return new ArrayCollection($array);
    }
}
