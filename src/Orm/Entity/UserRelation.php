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
use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Orm\Attribute\TruncateOnGameReset;
use Stu\Orm\Repository\UserRelationRepository;

#[Table(name: 'stu_user_relations')]
#[Index(name: 'user_relation_source_user_idx', columns: ['source_user_id'])]
#[Index(name: 'user_relation_recipient_user_idx', columns: ['recipient_user_id'])]
#[Index(name: 'user_relation_source_alliance_idx', columns: ['source_alliance_id'])]
#[Index(name: 'user_relation_recipient_alliance_idx', columns: ['recipient_alliance_id'])]
#[Entity(repositoryClass: UserRelationRepository::class)]
#[TruncateOnGameReset]
class UserRelation
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'smallint', enumType: AllianceRelationTypeEnum::class)]
    private AllianceRelationTypeEnum $type = AllianceRelationTypeEnum::FRIENDS;

    #[Column(type: 'integer', nullable: true)]
    private ?int $source_user_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $source_alliance_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $recipient_user_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $recipient_alliance_id = null;

    #[Column(type: 'integer')]
    private int $date = 0;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'source_user_id', nullable: true, referencedColumnName: 'id')]
    private ?User $sourceUser = null;

    #[ManyToOne(targetEntity: Alliance::class)]
    #[JoinColumn(name: 'source_alliance_id', nullable: true, referencedColumnName: 'id')]
    private ?Alliance $sourceAlliance = null;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'recipient_user_id', nullable: true, referencedColumnName: 'id')]
    private ?User $recipientUser = null;

    #[ManyToOne(targetEntity: Alliance::class)]
    #[JoinColumn(name: 'recipient_alliance_id', nullable: true, referencedColumnName: 'id')]
    private ?Alliance $recipientAlliance = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): AllianceRelationTypeEnum
    {
        return $this->type;
    }

    public function setType(AllianceRelationTypeEnum $type): UserRelation
    {
        $this->type = $type;
        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): UserRelation
    {
        $this->date = $date;
        return $this;
    }

    public function isPending(): bool
    {
        return $this->date === 0;
    }

    public function isWar(): bool
    {
        return $this->type === AllianceRelationTypeEnum::WAR;
    }

    public function getSourceUser(): ?User
    {
        return $this->sourceUser;
    }

    public function setSourceUser(?User $sourceUser): UserRelation
    {
        $this->sourceUser = $sourceUser;
        return $this;
    }

    public function getSourceAlliance(): ?Alliance
    {
        return $this->sourceAlliance;
    }

    public function setSourceAlliance(?Alliance $sourceAlliance): UserRelation
    {
        $this->sourceAlliance = $sourceAlliance;
        return $this;
    }

    public function getRecipientUser(): ?User
    {
        return $this->recipientUser;
    }

    public function setRecipientUser(?User $recipientUser): UserRelation
    {
        $this->recipientUser = $recipientUser;
        return $this;
    }

    public function getRecipientAlliance(): ?Alliance
    {
        return $this->recipientAlliance;
    }

    public function setRecipientAlliance(?Alliance $recipientAlliance): UserRelation
    {
        $this->recipientAlliance = $recipientAlliance;
        return $this;
    }

    public function isSourceParty(User $user): bool
    {
        return ($this->sourceUser !== null && $this->sourceUser->getId() === $user->getId())
            || ($this->sourceAlliance !== null
                && $user->getAlliance() !== null
                && $this->sourceAlliance->getId() === $user->getAlliance()->getId());
    }

    public function isRecipientParty(User $user): bool
    {
        return ($this->recipientUser !== null && $this->recipientUser->getId() === $user->getId())
            || ($this->recipientAlliance !== null
                && $user->getAlliance() !== null
                && $this->recipientAlliance->getId() === $user->getAlliance()->getId());
    }

    public function getSourceName(): string
    {
        return $this->sourceUser?->getName() ?? $this->sourceAlliance?->getName() ?? '';
    }

    public function getRecipientName(): string
    {
        return $this->recipientUser?->getName() ?? $this->recipientAlliance?->getName() ?? '';
    }

    public function getCounterpartName(User $user): string
    {
        return $this->isSourceParty($user)
            ? $this->getRecipientName()
            : $this->getSourceName();
    }
}
