<?php

declare(strict_types=1);

namespace Stu\Component\Player\Relation;

use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\Relation;
use Stu\Orm\Entity\User;

final class UserRelationPeaceManager
{
    public function __construct(
        private readonly UserRelationAccessChecker $accessChecker,
        private readonly UserRelationCreator $relationCreator,
        private readonly UserRelationMessenger $messenger
    ) {}

    public function suggest(User $actor, Relation $relation): bool
    {
        if (!$relation->isWar() || $relation->isPending()) {
            return false;
        }

        $source = $relation->getSourceParty();
        $recipient = $relation->getRecipientParty();
        $actorRepresentsSource = $this->accessChecker->canRepresentParty($actor, $source);
        if (!$actorRepresentsSource && !$this->accessChecker->canRepresentParty($actor, $recipient)) {
            return false;
        }

        if (!$actorRepresentsSource) {
            [$source, $recipient] = [$recipient, $source];
        }
        if ($this->hasOffer($source, $recipient)) {
            return false;
        }

        $this->relationCreator->create($source, $recipient, AllianceRelationTypeEnum::PEACE);
        $this->messenger->sendToParty(
            $recipient,
            sprintf(
                '%s hat %s ein Friedensabkommen angeboten',
                $this->messenger->describeParty($source),
                $this->messenger->describeParty($recipient)
            )
        );

        return true;
    }

    private function hasOffer(User|Alliance $source, User|Alliance $recipient): bool
    {
        foreach ($this->relationCreator->getByParties($source, $recipient) as $relation) {
            if ($relation->getType() === AllianceRelationTypeEnum::PEACE) {
                return true;
            }
        }

        return false;
    }
}
