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
use LogicException;
use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Orm\Attribute\TruncateOnGameReset;
use Stu\Orm\Repository\RelationRepository;

#[Table(name: 'stu_relations')]
#[Index(name: 'relation_source_user_idx', columns: ['source_user_id'])]
#[Index(name: 'relation_recipient_user_idx', columns: ['recipient_user_id'])]
#[Index(name: 'relation_source_alliance_idx', columns: ['source_alliance_id'])]
#[Index(name: 'relation_recipient_alliance_idx', columns: ['recipient_alliance_id'])]
#[Entity(repositoryClass: RelationRepository::class)]
#[TruncateOnGameReset]
class Relation
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'smallint', enumType: AllianceRelationTypeEnum::class)]
    private AllianceRelationTypeEnum $type = AllianceRelationTypeEnum::FRIENDS;

    #[Column(type: 'integer')]
    private int $date = 0;

    #[Column(type: 'text', nullable: true)]
    private ?string $text = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $last_edited = null;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'source_user_id', referencedColumnName: 'id', nullable: true)]
    private ?User $sourceUser = null;

    #[ManyToOne(targetEntity: Alliance::class)]
    #[JoinColumn(name: 'source_alliance_id', referencedColumnName: 'id', nullable: true)]
    private ?Alliance $sourceAlliance = null;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'recipient_user_id', referencedColumnName: 'id', nullable: true)]
    private ?User $recipientUser = null;

    #[ManyToOne(targetEntity: Alliance::class)]
    #[JoinColumn(name: 'recipient_alliance_id', referencedColumnName: 'id', nullable: true)]
    private ?Alliance $recipientAlliance = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): AllianceRelationTypeEnum
    {
        return $this->type;
    }

    public function setType(AllianceRelationTypeEnum $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): self
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

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): self
    {
        $this->text = $text;
        return $this;
    }

    public function getLastEdited(): ?int
    {
        return $this->last_edited;
    }

    public function setLastEdited(?int $lastEdited): self
    {
        $this->last_edited = $lastEdited;
        return $this;
    }

    public function hasText(): bool
    {
        return $this->text !== null && trim($this->text) !== '';
    }

    public function getSourceUser(): ?User
    {
        return $this->sourceUser;
    }

    public function setSourceUser(?User $sourceUser): self
    {
        $this->sourceUser = $sourceUser;
        return $this;
    }

    public function getSourceAlliance(): ?Alliance
    {
        return $this->sourceAlliance;
    }

    public function setSourceAlliance(?Alliance $sourceAlliance): self
    {
        $this->sourceAlliance = $sourceAlliance;
        return $this;
    }

    public function getRecipientUser(): ?User
    {
        return $this->recipientUser;
    }

    public function setRecipientUser(?User $recipientUser): self
    {
        $this->recipientUser = $recipientUser;
        return $this;
    }

    public function getRecipientAlliance(): ?Alliance
    {
        return $this->recipientAlliance;
    }

    public function setRecipientAlliance(?Alliance $recipientAlliance): self
    {
        $this->recipientAlliance = $recipientAlliance;
        return $this;
    }

    public function getSourceParty(): User|Alliance
    {
        return (
            $this->sourceUser ?? $this->sourceAlliance ?? throw new LogicException(
                'Eine Relation hat keine Ausgangspartei'
            )
        );
    }

    public function getRecipientParty(): User|Alliance
    {
        return (
            $this->recipientUser ?? $this->recipientAlliance ?? throw new LogicException(
                'Eine Relation hat keine Zielpartei'
            )
        );
    }

    public function validateParties(): void
    {
        if (($this->sourceUser === null) === ($this->sourceAlliance === null)) {
            throw new LogicException('Eine Relation muss genau einen Spieler oder eine Allianz als Ausgangspartei haben');
        }
        if (($this->recipientUser === null) === ($this->recipientAlliance === null)) {
            throw new LogicException('Eine Relation muss genau einen Spieler oder eine Allianz als Zielpartei haben');
        }

        $source = $this->getSourceParty();
        $recipient = $this->getRecipientParty();
        if ($source::class === $recipient::class && $source->getId() === $recipient->getId()) {
            throw new LogicException('Die Parteien einer Relation müssen verschieden sein');
        }
    }

    public function isSourceParty(User $user): bool
    {
        return (
            $this->sourceUser !== null
            && $this->sourceUser->getId() === $user->getId()
            || $this->sourceAlliance !== null
            && $user->getAlliance()?->getId() === $this->sourceAlliance->getId()
        );
    }

    public function getCounterpartName(User $user): string
    {
        return $this->isSourceParty($user)
            ? $this->getRecipientParty()->getName()
            : $this->getSourceParty()->getName();
    }

    public function getAlliance(): Alliance
    {
        return $this->sourceAlliance ?? throw new LogicException('Die Ausgangspartei ist keine Allianz');
    }

    public function setAlliance(Alliance $alliance): self
    {
        return $this->setSourceAlliance($alliance);
    }

    public function getOpponent(): Alliance
    {
        return $this->recipientAlliance ?? throw new LogicException('Die Zielpartei ist keine Allianz');
    }

    public function setOpponent(Alliance $opponent): self
    {
        return $this->setRecipientAlliance($opponent);
    }

    public function getAllianceId(): int
    {
        return $this->getAlliance()->getId();
    }

    public function getOpponentId(): int
    {
        return $this->getOpponent()->getId();
    }
}
