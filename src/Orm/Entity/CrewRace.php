<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\CrewRaceRepository;

#[Table(name: 'stu_crew_race')]
#[Entity(repositoryClass: CrewRaceRepository::class)]
class CrewRace
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    /** @var list<int>|null */
    #[Column(name: 'faction_id', type: 'json', nullable: true)]
    private ?array $faction_ids = null;

    #[Column(type: 'string')]
    private string $description = '';

    #[Column(type: 'smallint')]
    private int $chance = 0;

    #[Column(type: 'smallint')]
    private int $maleratio = 0;

    #[Column(type: 'string')]
    private string $define = '';

    #[Column(name: 'user_id', type: 'integer', nullable: true)]
    private ?int $creator_user_id = null;

    #[Column(type: 'boolean', options: ['default' => false])]
    private bool $shared = false;

    #[Column(type: 'boolean', options: ['default' => false])]
    private bool $accepted = false;

    #[Column(name: 'accepted_user_id', type: 'integer', nullable: true)]
    private ?int $accepted_user_id = null;

    public function getId(): int
    {
        return $this->id;
    }

    /** @return list<int> */
    public function getFactionIds(): array
    {
        return array_values(array_unique(array_map('intval', $this->faction_ids ?? [])));
    }

    /** @param list<int> $factionIds */
    public function setFactionIds(array $factionIds): CrewRace
    {
        $this->faction_ids = array_values(array_unique(array_map('intval', $factionIds)));

        return $this;
    }

    public function hasFactionId(int $factionId): bool
    {
        return in_array($factionId, $this->getFactionIds(), true);
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): CrewRace
    {
        $this->description = $description;

        return $this;
    }

    public function getChance(): int
    {
        return $this->chance;
    }

    public function setChance(int $chance): CrewRace
    {
        $this->chance = $chance;

        return $this;
    }

    public function getMaleRatio(): int
    {
        return $this->maleratio;
    }

    public function setMaleRatio(int $maleRatio): CrewRace
    {
        $this->maleratio = $maleRatio;

        return $this;
    }

    public function getGfxPath(): string
    {
        return $this->define;
    }

    public function setGfxPath(string $gfxPath): CrewRace
    {
        $this->define = $gfxPath;

        return $this;
    }

    public function getCreatorUserId(): ?int
    {
        return $this->creator_user_id;
    }

    public function setCreatorUserId(?int $creatorUserId): CrewRace
    {
        $this->creator_user_id = $creatorUserId;

        return $this;
    }

    public function isShared(): bool
    {
        return $this->shared;
    }

    public function setShared(bool $shared): CrewRace
    {
        $this->shared = $shared;

        return $this;
    }

    public function isAccepted(): bool
    {
        return $this->accepted;
    }

    public function setAccepted(bool $accepted): CrewRace
    {
        $this->accepted = $accepted;

        return $this;
    }

    public function getAcceptedUserId(): ?int
    {
        return $this->accepted_user_id;
    }

    public function setAcceptedUserId(?int $acceptedUserId): CrewRace
    {
        $this->accepted_user_id = $acceptedUserId;

        return $this;
    }

    public function isCustom(): bool
    {
        return $this->creator_user_id !== null;
    }

    public function isRejected(): bool
    {
        return !$this->accepted && $this->accepted_user_id !== null;
    }

    public function getImagePath(string $gender, int $imageType): string
    {
        $basePath = $this->isCustom() ? '/avatare/user/crew' : '/assets/crew';

        return sprintf('%s/%s/%s/1_%d.png', $basePath, $this->define, $gender, $imageType);
    }

    public function getStatus(): string
    {
        if ($this->accepted) {
            return 'Akzeptiert';
        }

        return $this->isRejected() ? 'Abgelehnt' : 'Wartet auf Freigabe';
    }

    public function canEditDistribution(): bool
    {
        return $this->isCustom() && $this->accepted;
    }
}
