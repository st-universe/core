<?php

declare(strict_types=1);

namespace Stu\Component\Player\Relation;

use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\Relation;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\RelationRepositoryInterface;

final class UserRelationLifecycleManager
{
    public function __construct(
        private readonly RelationRepositoryInterface $relationRepository,
        private readonly UserRelationAccessChecker $accessChecker,
        private readonly UserRelationCreator $relationCreator,
        private readonly UserRelationMessenger $messenger,
        private readonly UserRelationHistory $history
    ) {}

    public function accept(User $actor, Relation $relation): bool
    {
        if (!$relation->isPending() || !$this->accessChecker->canRepresentParty($actor, $relation->getRecipientParty())) {
            return false;
        }

        $this->removeExistingRelations($relation);
        $relation->setDate(time());
        $this->relationRepository->save($relation);

        $text = $this->getConclusionText($relation);
        $this->messenger->sendToParty($relation->getSourceParty(), $text);
        $this->history->add($relation, $actor->getId(), $text);

        return true;
    }

    public function cancel(User $actor, Relation $relation): bool
    {
        if ($relation->isWar()) {
            return false;
        }

        $source = $relation->getSourceParty();
        $recipient = $relation->getRecipientParty();
        if ($relation->isPending()) {
            return $this->withdrawOffer($actor, $relation, $source, $recipient);
        }

        return $this->endRelation($actor, $relation, $source, $recipient);
    }

    public function decline(User $actor, Relation $relation): bool
    {
        if (!$relation->isPending() || !$this->accessChecker->canRepresentParty($actor, $relation->getRecipientParty())) {
            return false;
        }

        $this->relationRepository->delete($relation);
        $this->messenger->sendToParty(
            $relation->getSourceParty(),
            sprintf(
                '%s hat das Angebot für ein %s abgelehnt',
                $this->messenger->describeParty($relation->getRecipientParty()),
                $relation->getType()->getDescription()
            )
        );

        return true;
    }

    private function removeExistingRelations(Relation $relation): void
    {
        foreach ($this->relationCreator->getByParties(
            $relation->getSourceParty(),
            $relation->getRecipientParty()
        ) as $existingRelation) {
            if (!$existingRelation->isPending() && $existingRelation->getId() !== $relation->getId()) {
                $this->relationRepository->delete($existingRelation);
            }
        }
    }

    private function withdrawOffer(
        User $actor,
        Relation $relation,
        User|Alliance $source,
        User|Alliance $recipient
    ): bool {
        if (!$this->accessChecker->canRepresentParty($actor, $source)) {
            return false;
        }

        $this->relationRepository->delete($relation);
        $this->messenger->sendToParty(
            $recipient,
            sprintf(
                '%s hat das Angebot für ein %s zurückgezogen',
                $this->messenger->describeParty($source),
                $relation->getType()->getDescription()
            )
        );

        return true;
    }

    private function endRelation(
        User $actor,
        Relation $relation,
        User|Alliance $source,
        User|Alliance $recipient
    ): bool {
        $actorRepresentsSource = $this->accessChecker->canRepresentParty($actor, $source);
        if (!$actorRepresentsSource && !$this->accessChecker->canRepresentParty($actor, $recipient)) {
            return false;
        }

        $this->relationRepository->delete($relation);
        $counterpart = $actorRepresentsSource ? $recipient : $source;
        $text = sprintf(
            '%s hat das %s aufgelöst',
            $this->messenger->describeParty($actorRepresentsSource ? $source : $recipient),
            $relation->getType()->getDescription()
        );
        $this->messenger->sendToParty($counterpart, $text);
        $this->history->add(
            $relation,
            $actor->getId(),
            sprintf(
                'Das %s zwischen %s und %s wurde aufgelöst',
                $relation->getType()->getDescription(),
                $this->messenger->describeParty($source),
                $this->messenger->describeParty($recipient)
            )
        );

        return true;
    }

    private function getConclusionText(Relation $relation): string
    {
        $source = $relation->getSourceParty();
        $recipient = $relation->getRecipientParty();

        if ($relation->getType() === AllianceRelationTypeEnum::VASSAL) {
            return sprintf(
                '%s ist nun Vasall von %s',
                $this->messenger->describeParty($recipient),
                $this->messenger->describeParty($source)
            );
        }

        return sprintf(
            '%s und %s sind ein %s eingegangen',
            $this->messenger->describeParty($source),
            $this->messenger->describeParty($recipient),
            $relation->getType()->getDescription()
        );
    }
}
