<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\AstroEntryRepository;

#[Table(name: 'stu_astro_entry')]
#[Index(name: 'astro_entry_user_idx', columns: ['user_id'])]
#[Index(name: 'astro_entry_star_system_idx', columns: ['systems_id'])]
#[Index(name: 'astro_entry_map_region_idx', columns: ['region_id'])]
#[Entity(repositoryClass: AstroEntryRepository::class)]
class AstronomicalEntry
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'smallint', length: 1)]
    private int $state = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $astro_start_turn = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $systems_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $region_id = null;

    #[Column(type: 'text')]
    private string $field_ids = '';

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    #[ManyToOne(targetEntity: StarSystem::class, inversedBy: 'astronomicalEntries')]
    #[JoinColumn(name: 'systems_id', referencedColumnName: 'id')]
    private ?StarSystem $starSystem = null;


    #[ManyToOne(targetEntity: MapRegion::class, inversedBy: 'astronomicalEntries')]
    #[JoinColumn(name: 'region_id', referencedColumnName: 'id')]
    private ?MapRegion $region = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): AstronomicalEntry
    {
        $this->user = $user;
        return $this;
    }

    public function getState(): int
    {
        return $this->state;
    }

    public function setState(int $state): AstronomicalEntry
    {
        $this->state = $state;
        return $this;
    }

    public function getAstroStartTurn(): ?int
    {
        return $this->astro_start_turn;
    }

    public function setAstroStartTurn(?int $turn): AstronomicalEntry
    {
        $this->astro_start_turn = $turn;
        return $this;
    }

    public function getSystem(): ?StarSystem
    {
        return $this->starSystem;
    }

    public function setSystem(StarSystem $starSystem): AstronomicalEntry
    {
        $this->starSystem = $starSystem;
        return $this;
    }

    public function getRegion(): ?MapRegion
    {
        return $this->region;
    }

    public function setRegion(MapRegion $region): AstronomicalEntry
    {
        $this->region = $region;
        return $this;
    }

    public function getFieldIds(): string
    {
        return $this->field_ids;
    }

    public function setFieldIds(string $fieldIds): AstronomicalEntry
    {
        $this->field_ids = $fieldIds;
        return $this;
    }

    public function isMeasured(): bool
    {
        return $this->getFieldIds() === '' || $this->getFieldIds() === '0';
    }
}
