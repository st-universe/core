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
use Doctrine\ORM\Mapping\Table;
use Stu\Lib\ModuleScreen\ModuleSelectWrapper;

#[Table(name: 'stu_buildplans')]
#[Entity(repositoryClass: 'Stu\Orm\Repository\ShipBuildplanRepository')]
class ShipBuildplan implements ShipBuildplanInterface
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
     * @var Collection<int, ShipInterface>
     */
    #[OneToMany(targetEntity: 'Ship', mappedBy: 'buildplan', fetch: 'EXTRA_LAZY')]
    private Collection $ships;

    #[ManyToOne(targetEntity: 'ShipRump')]
    #[JoinColumn(name: 'rump_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ShipRumpInterface $shipRump;

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

    /**
     * @var Collection<int, BuildplanModuleInterface>
     */
    #[OneToMany(targetEntity: 'BuildplanModule', mappedBy: 'buildplan', indexBy: 'module_id', fetch: 'EXTRA_LAZY')]
    private Collection $modules;

    public function __construct()
    {
        $this->ships = new ArrayCollection();
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

    public function setRumpId(int $shipRumpId): ShipBuildplanInterface
    {
        $this->rump_id = $shipRumpId;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): ShipBuildplanInterface
    {
        $this->user = $user;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): ShipBuildplanInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getBuildtime(): int
    {
        return $this->buildtime;
    }

    public function setBuildtime(int $buildtime): ShipBuildplanInterface
    {
        $this->buildtime = $buildtime;

        return $this;
    }

    public function getShipCount(): int
    {
        return $this->getShiplist()->count();
    }

    /**
     * @param array<int> $modules
     */
    public static function createSignature(array $modules, int $crewUsage = 0): string
    {
        return md5(implode('_', $modules) . '_' . $crewUsage);
    }

    public function getSignature(): ?string
    {
        return $this->signature;
    }

    public function setSignature(?string $signature): ShipBuildplanInterface
    {
        $this->signature = $signature;

        return $this;
    }

    public function getCrew(): int
    {
        return $this->crew;
    }

    public function setCrew(int $crew): ShipBuildplanInterface
    {
        $this->crew = $crew;

        return $this;
    }

    public function getShiplist(): Collection
    {
        return $this->ships;
    }

    public function getRump(): ShipRumpInterface
    {
        return $this->shipRump;
    }

    public function setRump(ShipRumpInterface $shipRump): ShipBuildplanInterface
    {
        $this->shipRump = $shipRump;

        return $this;
    }

    public function getModulesByType(int $type): array
    {
        return $this->getModules()
            ->filter(
                fn (BuildplanModuleInterface $buildplanModule): bool => $buildplanModule->getModuleType() === $type
            )
            ->toArray();
    }

    public function getModules(): Collection
    {
        return $this->modules;
    }

    public function getModule(): ModuleSelectWrapper
    {
        return new ModuleSelectWrapper($this);
    }
}
