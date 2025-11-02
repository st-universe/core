<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\NPCQuestRepository;

#[Table(name: 'stu_npc_quests')]
#[Index(name: 'npc_quest_user_idx', columns: ['user_id'])]
#[Index(name: 'npc_quest_award_idx', columns: ['award_id'])]
#[Index(name: 'npc_quest_plot_idx', columns: ['plot_id'])]
#[Entity(repositoryClass: NPCQuestRepository::class)]
class NPCQuest
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'text')]
    private string $title = '';

    #[Column(type: 'text')]
    private string $text = '';

    /** @var ?array<int> */
    #[Column(type: 'json', nullable: true)]
    private ?array $factions = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $prestige = null;

    /** @var ?array<int, int> */
    #[Column(type: 'json', nullable: true)]
    private ?array $commodity_reward = null;

    /** @var ?array<int, int> */
    #[Column(type: 'json', nullable: true)]
    private ?array $spacecrafts = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $award_id = null;

    #[Column(type: 'integer')]
    private int $time = 0;

    #[Column(type: 'integer')]
    private int $start = 0;

    #[Column(type: 'integer')]
    private int $application_end = 0;

    #[Column(name: '"end"', type: 'integer', nullable: true)]
    private ?int $end = null;

    /** @var ?array<int> */
    #[Column(type: 'json', nullable: true)]
    private ?array $secret = null;

    #[Column(type: 'boolean')]
    private bool $approval_required = false;

    #[Column(type: 'integer', nullable: true)]
    private ?int $applicant_max = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $plot_id = null;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ManyToOne(targetEntity: Award::class)]
    #[JoinColumn(name: 'award_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private ?Award $award = null;

    #[ManyToOne(targetEntity: RpgPlot::class)]
    #[JoinColumn(name: 'plot_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private ?RpgPlot $plot = null;

    /**
     * @var Collection<int, NPCQuestUser>
     */
    #[OneToMany(targetEntity: NPCQuestUser::class, mappedBy: 'quest')]
    private Collection $questUsers;

    public function __construct()
    {
        $this->questUsers = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $userId): NPCQuest
    {
        $this->user_id = $userId;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): NPCQuest
    {
        $this->user = $user;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): NPCQuest
    {
        $this->title = $title;
        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): NPCQuest
    {
        $this->text = $text;
        return $this;
    }

    /** @return ?array<int> */
    public function getFactions(): ?array
    {
        return $this->factions;
    }

    /** @param ?array<int> $factions */
    public function setFactions(?array $factions): NPCQuest
    {
        $this->factions = $factions;
        return $this;
    }

    public function getPrestige(): ?int
    {
        return $this->prestige;
    }

    public function setPrestige(?int $prestige): NPCQuest
    {
        $this->prestige = $prestige;
        return $this;
    }

    /** @return ?array<int, int> */
    public function getCommodityReward(): ?array
    {
        return $this->commodity_reward;
    }

    /** @param ?array<int, int> $commodityReward */
    public function setCommodityReward(?array $commodityReward): NPCQuest
    {
        $this->commodity_reward = $commodityReward;
        return $this;
    }

    /** @return ?array<int, int> */
    public function getSpacecrafts(): ?array
    {
        return $this->spacecrafts;
    }

    /** @param ?array<int, int> $spacecrafts */
    public function setSpacecrafts(?array $spacecrafts): NPCQuest
    {
        $this->spacecrafts = $spacecrafts;
        return $this;
    }

    public function getAwardId(): ?int
    {
        return $this->award_id;
    }

    public function setAwardId(?int $awardId): NPCQuest
    {
        $this->award_id = $awardId;
        return $this;
    }

    public function getAward(): ?Award
    {
        return $this->award;
    }

    public function setAward(?Award $award): NPCQuest
    {
        $this->award = $award;
        return $this;
    }

    public function getTime(): int
    {
        return $this->time;
    }

    public function setTime(int $time): NPCQuest
    {
        $this->time = $time;
        return $this;
    }

    public function getStart(): int
    {
        return $this->start;
    }

    public function setStart(int $start): NPCQuest
    {
        $this->start = $start;
        return $this;
    }

    public function getApplicationEnd(): int
    {
        return $this->application_end;
    }

    public function setApplicationEnd(int $applicationEnd): NPCQuest
    {
        $this->application_end = $applicationEnd;
        return $this;
    }

    public function getEnd(): ?int
    {
        return $this->end;
    }

    public function setEnd(?int $end): NPCQuest
    {
        $this->end = $end;
        return $this;
    }

    /** @return ?array<int> */
    public function getSecret(): ?array
    {
        return $this->secret;
    }

    /** @param ?array<int> $secret */
    public function setSecret(?array $secret): NPCQuest
    {
        $this->secret = $secret;
        return $this;
    }

    public function isApprovalRequired(): bool
    {
        return $this->approval_required;
    }

    public function setApprovalRequired(bool $approvalRequired): NPCQuest
    {
        $this->approval_required = $approvalRequired;
        return $this;
    }

    public function getApplicantMax(): ?int
    {
        return $this->applicant_max;
    }

    public function setApplicantMax(?int $applicantMax): NPCQuest
    {
        $this->applicant_max = $applicantMax;
        return $this;
    }

    public function getPlotId(): ?int
    {
        return $this->plot_id;
    }

    public function setPlotId(?int $plotId): NPCQuest
    {
        $this->plot_id = $plotId;
        return $this;
    }

    public function getPlot(): ?RpgPlot
    {
        return $this->plot;
    }

    public function setPlot(?RpgPlot $plot): NPCQuest
    {
        $this->plot = $plot;
        return $this;
    }

    /**
     * @return Collection<int, NPCQuestUser>
     */
    public function getQuestUsers(): Collection
    {
        return $this->questUsers;
    }
}
