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
use Stu\Module\Colony\View\Sandbox\ShowColonySandbox;
use Stu\Orm\Repository\ColonySandboxRepository;

#[Table(name: 'stu_colony_sandbox')]
#[Entity(repositoryClass: ColonySandboxRepository::class)]
class ColonySandbox implements PlanetFieldHostInterface
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
     * @var ArrayCollection<int, PlanetField>
     */
    #[OneToMany(targetEntity: PlanetField::class, mappedBy: 'sandbox', indexBy: 'field_id', fetch: 'EXTRA_LAZY')]
    #[OrderBy(['field_id' => 'ASC'])]
    private Collection $planetFields;

    #[ManyToOne(targetEntity: Colony::class)]
    #[JoinColumn(name: 'colony_id', nullable: false, referencedColumnName: 'id')]
    private Colony $colony;

    public function __construct()
    {
        $this->planetFields = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->getColony()->getUser();
    }

    public function getColony(): Colony
    {
        return $this->colony;
    }

    public function setColony(Colony $colony): ColonySandbox
    {
        $this->colony = $colony;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): ColonySandbox
    {
        $this->name = $name;
        return $this;
    }

    public function getWorkers(): int
    {
        return $this->bev_work;
    }

    public function setWorkers(int $bev_work): ColonySandbox
    {
        $this->bev_work = $bev_work;
        return $this;
    }

    public function getMaxBev(): int
    {
        return $this->bev_max;
    }

    public function setMaxBev(int $bev_max): ColonySandbox
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

    public function setMaxEps(int $max_eps): ColonySandbox
    {
        $this->max_eps = $max_eps;
        return $this;
    }

    public function getMaxStorage(): int
    {
        return $this->max_storage;
    }

    public function setMaxStorage(int $max_storage): ColonySandbox
    {
        $this->max_storage = $max_storage;
        return $this;
    }

    public function getMask(): ?string
    {
        return $this->mask;
    }

    public function setMask(?string $mask): ColonySandbox
    {
        $this->mask = $mask;
        return $this;
    }

    public function getPlanetFields(): Collection
    {
        return $this->planetFields;
    }

    public function getTwilightZone(int $timestamp): int
    {
        return $this->getColony()->getTwilightZone($timestamp);
    }

    public function getSurfaceWidth(): int
    {
        return $this->getColony()->getSurfaceWidth();
    }

    public function getColonyClass(): ColonyClass
    {
        return $this->getColony()->getColonyClass();
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

    public function getComponentParameters(): string
    {
        return sprintf(
            '&hosttype=%d&id=%d',
            $this->getHostType()->value,
            $this->getId()
        );
    }
}
