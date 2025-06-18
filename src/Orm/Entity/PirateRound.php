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
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\ORM\Mapping\Table;
use Override;
use Stu\Orm\Repository\PirateRoundRepository;

#[Table(name: 'stu_pirate_round')]
#[Entity(repositoryClass: PirateRoundRepository::class)]
class PirateRound implements PirateRoundInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $start = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $end_time = null;

    #[Column(type: 'integer')]
    private int $max_prestige = 0;

    #[Column(type: 'integer')]
    private int $actual_prestige = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $faction_winner = null;

    /**
     * @var ArrayCollection<int, UserPirateRoundInterface>
     */
    #[OneToMany(targetEntity: 'UserPirateRound', mappedBy: 'pirateRound')]
    private Collection $userPirateRounds;

    public function __construct()
    {
        $this->userPirateRounds = new ArrayCollection();
    }

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getStart(): int
    {
        return $this->start;
    }

    #[Override]
    public function setStart(int $start): PirateRoundInterface
    {
        $this->start = $start;
        return $this;
    }

    #[Override]
    public function getEndTime(): ?int
    {
        return $this->end_time;
    }

    #[Override]
    public function setEndTime(?int $endTime): PirateRoundInterface
    {
        $this->end_time = $endTime;
        return $this;
    }

    #[Override]
    public function getMaxPrestige(): int
    {
        return $this->max_prestige;
    }

    #[Override]
    public function setMaxPrestige(int $maxPrestige): PirateRoundInterface
    {
        $this->max_prestige = $maxPrestige;
        return $this;
    }

    #[Override]
    public function getActualPrestige(): int
    {
        return $this->actual_prestige;
    }

    #[Override]
    public function setActualPrestige(int $actualPrestige): PirateRoundInterface
    {
        $this->actual_prestige = $actualPrestige;
        return $this;
    }

    #[Override]
    public function getFactionWinner(): ?int
    {
        return $this->faction_winner;
    }

    #[Override]
    public function setFactionWinner(?int $factionWinner): PirateRoundInterface
    {
        $this->faction_winner = $factionWinner;
        return $this;
    }

    #[Override]
    public function getUserPirateRounds(): Collection
    {
        return $this->userPirateRounds;
    }
}
