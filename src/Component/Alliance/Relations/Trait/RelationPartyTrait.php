<?php

declare(strict_types=1);

namespace Stu\Component\Alliance\Relations\Trait;

use LogicException;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\User;

trait RelationPartyTrait
{
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
            throw new LogicException(
                'Eine Relation muss genau einen Spieler oder eine Allianz als Ausgangspartei haben'
            );
        }
        if (($this->recipientUser === null) === ($this->recipientAlliance === null)) {
            throw new LogicException(
                'Eine Relation muss genau einen Spieler oder eine Allianz als Zielpartei haben'
            );
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

    public function isRecipientParty(User $user): bool
    {
        return (
            $this->recipientUser !== null
            && $this->recipientUser->getId() === $user->getId()
            || $this->recipientAlliance !== null
            && $user->getAlliance()?->getId() === $this->recipientAlliance->getId()
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
