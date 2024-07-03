<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Orm\Repository\AstroEntryRepository;
use Override;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_astro_entry')]
#[Index(name: 'astro_entry_user_idx', columns: ['user_id'])]
#[Index(name: 'astro_entry_star_system_idx', columns: ['systems_id'])]
#[Index(name: 'astro_entry_map_region_idx', columns: ['region_id'])]
#[Entity(repositoryClass: AstroEntryRepository::class)]
class AstronomicalEntry implements AstronomicalEntryInterface
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

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

    #[ManyToOne(targetEntity: 'StarSystem')]
    #[JoinColumn(name: 'systems_id', referencedColumnName: 'id')]
    private ?StarSystemInterface $starSystem = null;


    #[ManyToOne(targetEntity: 'MapRegion')]
    #[JoinColumn(name: 'region_id', referencedColumnName: 'id')]
    private ?MapRegionInterface $region = null;

    #[Override]
    public function getId(): int
    {
        return $this->id;
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
    public function setUser(UserInterface $user): AstronomicalEntryInterface
    {
        $this->user = $user;
        return $this;
    }

    #[Override]
    public function getState(): int
    {
        return $this->state;
    }

    #[Override]
    public function setState(int $state): AstronomicalEntryInterface
    {
        $this->state = $state;
        return $this;
    }

    #[Override]
    public function getAstroStartTurn(): ?int
    {
        return $this->astro_start_turn;
    }

    #[Override]
    public function setAstroStartTurn(?int $turn): AstronomicalEntryInterface
    {
        $this->astro_start_turn = $turn;
        return $this;
    }

    #[Override]
    public function getSystem(): ?StarSystemInterface
    {
        return $this->starSystem;
    }

    #[Override]
    public function setSystem(StarSystemInterface $starSystem): AstronomicalEntryInterface
    {
        $this->starSystem = $starSystem;
        return $this;
    }

    #[Override]
    public function getRegion(): ?MapRegionInterface
    {
        return $this->region;
    }

    #[Override]
    public function setRegion(MapRegionInterface $region): AstronomicalEntryInterface
    {
        $this->region = $region;
        return $this;
    }

    #[Override]
    public function getFieldIds(): string
    {
        return $this->field_ids;
    }

    #[Override]
    public function setFieldIds(string $fieldIds): AstronomicalEntryInterface
    {
        $this->field_ids = $fieldIds;
        return $this;
    }

    #[Override]
    public function isMeasured(): bool
    {
        return $this->getFieldIds() === '' || $this->getFieldIds() === '0';
    }
}
