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
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\PirateRoundRepository;

#[Table(name: 'stu_pirate_round')]
#[Entity(repositoryClass: PirateRoundRepository::class)]
class PirateRound
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
     * @var ArrayCollection<int, UserPirateRound>
     */
    #[OneToMany(targetEntity: UserPirateRound::class, mappedBy: 'pirateRound')]
    private Collection $userPirateRounds;

    public function __construct()
    {
        $this->userPirateRounds = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getStart(): int
    {
        return $this->start;
    }

    public function setStart(int $start): PirateRound
    {
        $this->start = $start;
        return $this;
    }

    public function getEndTime(): ?int
    {
        return $this->end_time;
    }

    public function setEndTime(?int $endTime): PirateRound
    {
        $this->end_time = $endTime;
        return $this;
    }

    public function getMaxPrestige(): int
    {
        return $this->max_prestige;
    }

    public function setMaxPrestige(int $maxPrestige): PirateRound
    {
        $this->max_prestige = $maxPrestige;
        return $this;
    }

    public function getActualPrestige(): int
    {
        return $this->actual_prestige;
    }

    public function setActualPrestige(int $actualPrestige): PirateRound
    {
        $this->actual_prestige = $actualPrestige;
        return $this;
    }

    public function getFactionWinner(): ?int
    {
        return $this->faction_winner;
    }

    public function setFactionWinner(?int $factionWinner): PirateRound
    {
        $this->faction_winner = $factionWinner;
        return $this;
    }

    /**
     * @return Collection<int, UserPirateRound>
     */
    public function getUserPirateRounds(): Collection
    {
        return $this->userPirateRounds;
    }
}
