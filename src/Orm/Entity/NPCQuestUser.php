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
use Stu\Orm\Repository\NPCQuestUserRepository;
use Stu\Component\Quest\QuestUserModeEnum;

#[Table(name: 'stu_npc_quest_user')]
#[Index(name: 'npc_quest_user_quest_idx', columns: ['quest_id'])]
#[Index(name: 'npc_quest_user_user_idx', columns: ['user_id'])]
#[Entity(repositoryClass: NPCQuestUserRepository::class)]
class NPCQuestUser
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $quest_id = 0;

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'integer', enumType: QuestUserModeEnum::class)]
    private QuestUserModeEnum $mode = QuestUserModeEnum::APPLICANT;

    #[Column(type: 'boolean', options: ['default' => false])]
    private bool $reward_received = false;

    #[ManyToOne(targetEntity: NPCQuest::class, inversedBy: 'questUsers')]
    #[JoinColumn(name: 'quest_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?NPCQuest $quest = null;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?User $user = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getQuestId(): int
    {
        return $this->quest_id;
    }

    public function setQuestId(int $questId): NPCQuestUser
    {
        $this->quest_id = $questId;
        return $this;
    }

    public function getQuest(): ?NPCQuest
    {
        return $this->quest;
    }

    public function setQuest(?NPCQuest $quest): NPCQuestUser
    {
        $this->quest = $quest;
        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $userId): NPCQuestUser
    {
        $this->user_id = $userId;
        return $this;
    }

    public function getMode(): QuestUserModeEnum
    {
        return $this->mode;
    }

    public function setMode(QuestUserModeEnum $mode): NPCQuestUser
    {
        $this->mode = $mode;
        return $this;
    }

    public function isRewardReceived(): bool
    {
        return $this->reward_received;
    }

    public function setRewardReceived(bool $rewardReceived): NPCQuestUser
    {
        $this->reward_received = $rewardReceived;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): NPCQuestUser
    {
        $this->user = $user;
        return $this;
    }
}
