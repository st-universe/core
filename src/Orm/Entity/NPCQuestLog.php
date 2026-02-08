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
use Stu\Orm\Repository\NPCQuestLogRepository;

#[Table(name: 'stu_npc_quest_log')]
#[Index(name: 'npc_quest_log_quest_idx', columns: ['quest_id'])]
#[Index(name: 'npc_quest_log_user_idx', columns: ['user_id'])]
#[Entity(repositoryClass: NPCQuestLogRepository::class)]
class NPCQuestLog
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $quest_id = 0;

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'text')]
    private string $text = '';

    #[Column(type: 'integer')]
    private int $date = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $deleted = null;

    #[Column(type: 'integer')]
    private int $mode = 0;

    #[ManyToOne(targetEntity: NPCQuest::class)]
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

    public function setQuestId(int $questId): NPCQuestLog
    {
        $this->quest_id = $questId;
        return $this;
    }

    public function getQuest(): ?NPCQuest
    {
        return $this->quest;
    }

    public function setQuest(?NPCQuest $quest): NPCQuestLog
    {
        $this->quest = $quest;
        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $userId): NPCQuestLog
    {
        $this->user_id = $userId;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): NPCQuestLog
    {
        $this->user = $user;
        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): NPCQuestLog
    {
        $this->text = $text;
        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): NPCQuestLog
    {
        $this->date = $date;
        return $this;
    }

    public function getDeleted(): ?int
    {
        return $this->deleted;
    }

    public function setDeleted(?int $deleted): NPCQuestLog
    {
        $this->deleted = $deleted;
        return $this;
    }

    public function getMode(): int
    {
        return $this->mode;
    }

    public function setMode(int $mode): NPCQuestLog
    {
        $this->mode = $mode;
        return $this;
    }
}
