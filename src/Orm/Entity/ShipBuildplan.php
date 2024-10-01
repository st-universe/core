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
use Override;
use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Orm\Repository\ShipBuildplanRepository;

#[Table(name: 'stu_buildplans')]
#[Entity(repositoryClass: ShipBuildplanRepository::class)]
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
    public function setRumpId(int $shipRumpId): ShipBuildplanInterface
    {
        $this->rump_id = $shipRumpId;

        return $this;
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
    public function setUser(UserInterface $user): ShipBuildplanInterface
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
    public function setName(string $name): ShipBuildplanInterface
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
    public function setBuildtime(int $buildtime): ShipBuildplanInterface
    {
        $this->buildtime = $buildtime;

        return $this;
    }

    #[Override]
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

    #[Override]
    public function getSignature(): ?string
    {
        return $this->signature;
    }

    #[Override]
    public function setSignature(?string $signature): ShipBuildplanInterface
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
    public function setCrew(int $crew): ShipBuildplanInterface
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
    public function getRump(): ShipRumpInterface
    {
        return $this->shipRump;
    }

    #[Override]
    public function setRump(ShipRumpInterface $shipRump): ShipBuildplanInterface
    {
        $this->shipRump = $shipRump;

        return $this;
    }

    #[Override]
    public function getModulesByType(ShipModuleTypeEnum $type): array
    {
        return $this->getModules()
            ->filter(
                fn(BuildplanModuleInterface $buildplanModule): bool => $buildplanModule->getModuleType() === $type
            )
            ->toArray();
    }

    #[Override]
    public function getModules(): Collection
    {
        return $this->modules;
    }
}
