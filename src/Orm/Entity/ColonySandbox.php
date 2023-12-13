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
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Lib\Colony\PlanetFieldHostTypeEnum;
use Stu\Module\Admin\View\Sandbox\ShowColonySandbox;

#[Table(name: 'stu_colony_sandbox')]
#[Entity(repositoryClass: 'Stu\Orm\Repository\ColonySandboxRepository')]
class ColonySandbox implements ColonySandboxInterface, PlanetFieldHostInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $colony_id;

    #[Column(type: 'string')]
    private string $name = '';

    #[Column(type: 'integer', length: 5)]
    private int $bev_work = 0;

    #[Column(type: 'integer', length: 5)]
    private int $bev_max = 0;

    #[Column(type: 'integer', length: 5)]
    private int $max_eps = 0;

    #[Column(type: 'integer', length: 5)]
    private int $max_storage = 0;

    #[Column(type: 'text', nullable: true)]
    private ?string $mask = null;

    /**
     * @var ArrayCollection<int, PlanetFieldInterface>
     */
    #[OneToMany(targetEntity: 'PlanetField', mappedBy: 'sandbox', indexBy: 'field_id', fetch: 'EXTRA_LAZY')]
    #[OrderBy(['field_id' => 'ASC'])]
    private Collection $planetFields;

    #[ManyToOne(targetEntity: 'Colony')]
    #[JoinColumn(name: 'colony_id', referencedColumnName: 'id')]
    private ColonyInterface $colony;

    public function __construct()
    {
        $this->planetFields = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): UserInterface
    {
        return $this->getColony()->getUser();
    }

    public function getColony(): ColonyInterface
    {
        return $this->colony;
    }

    public function setColony(ColonyInterface $colony): ColonySandboxInterface
    {
        $this->colony = $colony;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): ColonySandboxInterface
    {
        $this->name = $name;
        return $this;
    }

    public function getWorkers(): int
    {
        return $this->bev_work;
    }

    public function setWorkers(int $bev_work): ColonySandboxInterface
    {
        $this->bev_work = $bev_work;
        return $this;
    }

    public function getMaxBev(): int
    {
        return $this->bev_max;
    }

    public function setMaxBev(int $bev_max): ColonySandboxInterface
    {
        $this->bev_max = $bev_max;
        return $this;
    }

    public function getPopulation(): int
    {
        return $this->getMaxBev();
    }

    public function getMaxEps(): int
    {
        return $this->max_eps;
    }

    public function setMaxEps(int $max_eps): ColonySandboxInterface
    {
        $this->max_eps = $max_eps;
        return $this;
    }

    public function getMaxStorage(): int
    {
        return $this->max_storage;
    }

    public function setMaxStorage(int $max_storage): ColonySandboxInterface
    {
        $this->max_storage = $max_storage;
        return $this;
    }

    public function getMask(): ?string
    {
        return $this->mask;
    }

    public function setMask(?string $mask): ColonySandboxInterface
    {
        $this->mask = $mask;
        return $this;
    }

    public function getPlanetFields(): Collection
    {
        return $this->planetFields;
    }

    public function getTwilightZone(): int
    {
        return $this->getColony()->getTwilightZone();
    }

    public function getSurfaceWidth(): int
    {
        return $this->getColony()->getSurfaceWidth();
    }

    public function getColonyClass(): ColonyClassInterface
    {
        return $this->getColony()->getColonyClass();
    }

    public function getPlanetFieldHostIdentifier(): string
    {
        return 'sandbox';
    }

    public function getPlanetFieldHostColumnIdentifier(): string
    {
        return 'colony_sandbox_id';
    }

    public function isColony(): bool
    {
        return false;
    }

    public function getHostType(): PlanetFieldHostTypeEnum
    {
        return PlanetFieldHostTypeEnum::SANDBOX;
    }

    public function getDefaultViewIdentifier(): string
    {
        return ShowColonySandbox::VIEW_IDENTIFIER;
    }

    public function isMenuAllowed(ColonyMenuEnum $menu): bool
    {
        return in_array($menu, [
            ColonyMenuEnum::MENU_MAINSCREEN,
            ColonyMenuEnum::MENU_BUILD,
            ColonyMenuEnum::MENU_BUILDINGS,
            ColonyMenuEnum::MENU_INFO,
            ColonyMenuEnum::MENU_SOCIAL
        ]);
    }
}
