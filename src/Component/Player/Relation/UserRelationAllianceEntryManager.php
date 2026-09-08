<?php

declare(strict_types=1);

namespace Stu\Component\Player\Relation;

use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\RelationRepositoryInterface;

final class UserRelationAllianceEntryManager
{
    public function __construct(
        private readonly RelationRepositoryInterface $relationRepository,
        private readonly UserRelationMessenger $messenger
    ) {}

    public function removeFor(User $user, Alliance $alliance, bool $isAllianceCreation): void
    {
        foreach ($this->relationRepository->getByUserAndAlliance($user, null) as $relation) {
            $source = $relation->getSourceParty();
            $recipient = $relation->getRecipientParty();
            $counterpart = $source instanceof User && $source->getId() === $user->getId() ? $recipient : $source;
            $text = sprintf(
                'Der Siedler %s %s. Das %s mit %s entfällt.',
                $user->getName(),
                $isAllianceCreation
                    ? sprintf('hat die Allianz %s gegründet', $alliance->getName())
                    : sprintf('ist der Allianz %s beigetreten', $alliance->getName()),
                $relation->getType()->getDescription(),
                $this->messenger->describeParty($counterpart)
            );

            $this->messenger->sendToParty($user, $text);
            $this->messenger->sendToParty($counterpart, $text);
            $this->relationRepository->delete($relation);
        }
    }
}
