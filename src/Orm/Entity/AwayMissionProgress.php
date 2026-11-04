<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Stu\Component\Quest\AwayMission\AwayMissionResearchEnum;

#[Table(name: 'stu_away_mission_progress')]
#[Entity]
#[UniqueConstraint(name: 'away_mission_progress_idx', columns: ['mission_id', 'user_id'])]
class AwayMissionProgress
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ManyToOne(targetEntity: AwayMission::class)]
    #[JoinColumn(name: 'mission_id', referencedColumnName: 'id', nullable: false)]
    private AwayMission $mission;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private User $user;

    /** @var list<int> */
    #[Column(type: 'json')]
    private array $scannedFields = [];

    #[Column(type: 'boolean')]
    private bool $started = false;

    #[Column(type: 'integer')]
    private int $lastPage = 0;

    #[Column(type: 'boolean')]
    private bool $rewardReceived = false;

    #[Column(type: 'string', nullable: true, enumType: AwayMissionResearchEnum::class)]
    private ?AwayMissionResearchEnum $researchSystem = null;

    #[ManyToOne(targetEntity: Faction::class)]
    #[JoinColumn(name: 'contact_faction_id', referencedColumnName: 'id', nullable: true)]
    private ?Faction $contactFaction = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $reportingCrewId = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getMission(): AwayMission
    {
        return $this->mission;
    }

    public function setMission(AwayMission $mission): self
    {
        $this->mission = $mission;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /** @return list<int> */
    public function getScannedFields(): array
    {
        return $this->scannedFields;
    }

    public function isStarted(): bool
    {
        return $this->started;
    }

    public function setStarted(bool $started): self
    {
        $this->started = $started;

        return $this;
    }

    public function getLastPage(): int
    {
        return $this->lastPage;
    }

    public function setLastPage(int $lastPage): self
    {
        $this->lastPage = $lastPage;

        return $this;
    }

    public function isRewardReceived(): bool
    {
        return $this->rewardReceived;
    }

    public function setRewardReceived(bool $rewardReceived): self
    {
        $this->rewardReceived = $rewardReceived;

        return $this;
    }

    public function scanField(int $fieldId): void
    {
        if (!in_array($fieldId, $this->scannedFields, true)) {
            $this->scannedFields[] = $fieldId;
        }
    }

    public function getResearchSystem(): ?AwayMissionResearchEnum
    {
        return $this->researchSystem;
    }

    public function setResearchSystem(AwayMissionResearchEnum $researchSystem): self
    {
        $this->researchSystem = $researchSystem;

        return $this;
    }

    public function getContactFaction(): ?Faction
    {
        return $this->contactFaction;
    }

    public function setContactFaction(Faction $contactFaction): self
    {
        $this->contactFaction = $contactFaction;

        return $this;
    }

    public function getReportingCrewId(): ?int
    {
        return $this->reportingCrewId;
    }

    public function setReportingCrewId(int $reportingCrewId): self
    {
        $this->reportingCrewId = $reportingCrewId;

        return $this;
    }
}
