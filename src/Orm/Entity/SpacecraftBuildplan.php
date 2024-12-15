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
use Override;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Orm\Repository\SpacecraftBuildplanRepository;

#[Table(name: 'stu_buildplan')]
#[Entity(repositoryClass: SpacecraftBuildplanRepository::class)]
#[UniqueConstraint(name: 'buildplan_signatures_idx', columns: ['user_id', 'rump_id', 'signature'])]
class SpacecraftBuildplan implements SpacecraftBuildplanInterface
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
     * @var ArrayCollection<int, ShipInterface>
     */
    #[OneToMany(targetEntity: 'Ship', mappedBy: 'buildplan', fetch: 'EXTRA_LAZY')]
    private Collection $ships;

    #[ManyToOne(targetEntity: 'SpacecraftRump')]
    #[JoinColumn(name: 'rump_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private SpacecraftRumpInterface $shipRump;

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

    /**
     * @var ArrayCollection<int, BuildplanModuleInterface>
     */
    #[OneToMany(targetEntity: 'BuildplanModule', mappedBy: 'buildplan', indexBy: 'module_id', fetch: 'EXTRA_LAZY')]
    #[OrderBy(['module_id' => 'ASC'])]
    private Collection $modules;

    public function __construct()
    {
        $this->ships = new ArrayCollection();
        $this->modules = new ArrayCollection();
    }

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getRumpId(): int
    {
        return $this->rump_id;
    }

    #[Override]
    public function getUserId(): int
    {
        return $this->user_id;
    }

    #[Override]
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    #[Override]
    public function setUser(UserInterface $user): SpacecraftBuildplanInterface
    {
        $this->user = $user;
        return $this;
    }

    #[Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[Override]
    public function setName(string $name): SpacecraftBuildplanInterface
    {
        $this->name = $name;

        return $this;
    }

    #[Override]
    public function getBuildtime(): int
    {
        return $this->buildtime;
    }

    #[Override]
    public function setBuildtime(int $buildtime): SpacecraftBuildplanInterface
    {
        $this->buildtime = $buildtime;

        return $this;
    }

    #[Override]
    public function getShipCount(): int
    {
        return $this->getShiplist()->count();
    }

    #[Override]
    public function getSignature(): ?string
    {
        return $this->signature;
    }

    #[Override]
    public function setSignature(?string $signature): SpacecraftBuildplanInterface
    {
        $this->signature = $signature;

        return $this;
    }

    #[Override]
    public function getCrew(): int
    {
        return $this->crew;
    }

    #[Override]
    public function setCrew(int $crew): SpacecraftBuildplanInterface
    {
        $this->crew = $crew;

        return $this;
    }

    #[Override]
    public function getShiplist(): Collection
    {
        return $this->ships;
    }

    #[Override]
    public function getRump(): SpacecraftRumpInterface
    {
        return $this->shipRump;
    }

    #[Override]
    public function setRump(SpacecraftRumpInterface $shipRump): SpacecraftBuildplanInterface
    {
        $this->shipRump = $shipRump;

        return $this;
    }

    #[Override]
    public function getModulesByType(SpacecraftModuleTypeEnum $type): Collection
    {
        return $this
            ->getModules()
            ->filter(fn(BuildplanModuleInterface $buildplanModule): bool => $buildplanModule->getModuleType() === $type)
            ->map(fn(BuildplanModuleInterface $buildplanModule): ModuleInterface => $buildplanModule->getModule());
    }

    #[Override]
    public function getModules(): Collection
    {
        $array = $this->modules->toArray();

        uasort($array, function (BuildplanModuleInterface $a, BuildplanModuleInterface $b): int {
            return $a->getModuleType()->getOrder() <=> $b->getModuleType()->getOrder();
        });

        return new ArrayCollection($array);
    }
}
