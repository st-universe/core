<?php

declare(strict_types=1);

namespace Stu\Component\Player\Relation;

use InvalidArgumentException;
use Stu\Component\Alliance\Enum\AllianceJobPermissionEnum;
use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Component\History\HistoryTypeEnum;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserRelation;
use Stu\Orm\Repository\UserRelationRepositoryInterface;

final class UserRelationManager implements UserRelationManagerInterface
{
    public function __construct(
        private readonly UserRelationRepositoryInterface $userRelationRepository,
        private readonly AllianceJobManagerInterface $allianceJobManager,
        private readonly AllianceActionManagerInterface $allianceActionManager,
        private readonly PrivateMessageSenderInterface $privateMessageSender,
        private readonly EntryCreatorInterface $entryCreator
    ) {}

    #[\Override]
    public function getRepresentedParty(User $user): User|Alliance|null
    {
        $alliance = $user->getAlliance();
        if ($alliance === null) {
            return $user;
        }

        if ($this->canCreateForAlliance($user, $alliance)) {
            return $alliance;
        }

        return null;
    }

    #[\Override]
    public function canManageRelations(User $user): bool
    {
        $alliance = $user->getAlliance();

        return $alliance === null || $this->canManageForAlliance($user, $alliance);
    }

    #[\Override]
    public function create(User $actor, User|Alliance $source, User|Alliance $recipient, AllianceRelationTypeEnum $type): ?UserRelation
    {
        if (
            !$this->canCreateForParty($actor, $source)
            || !$this->hasValidParties($source, $recipient)
            || $type === AllianceRelationTypeEnum::PEACE
        ) {
            return null;
        }

        $relations = $this->getRelationsByParties($source, $recipient);
        foreach ($relations as $relation) {
            if ($relation->getType() === $type) {
                return null;
            }
        }

        if ($type === AllianceRelationTypeEnum::WAR) {
            foreach ($relations as $relation) {
                $this->userRelationRepository->delete($relation);
            }

            $relation = $this->createRelation($source, $recipient, $type, time());
            $this->sendMessageToParty(
                $recipient,
                sprintf('%s hat %s den Krieg erklärt', $this->getPartyDescription($source), $this->getPartyDescription($recipient))
            );
            $this->addHistory($relation, $actor->getId(), sprintf('%s hat %s den Krieg erklärt', $this->getPartyDescription($source), $this->getPartyDescription($recipient)));

            return $relation;
        }

        if (count(array_filter($relations, static fn (UserRelation $relation): bool => $relation->isPending())) >= 2) {
            return null;
        }

        $relation = $this->createRelation($source, $recipient, $type);
        $this->sendMessageToParty(
            $recipient,
            sprintf(
                '%s hat %s ein %s angeboten',
                $this->getPartyDescription($source),
                $this->getPartyDescription($recipient),
                $type->getDescription()
            )
        );

        return $relation;
    }

    #[\Override]
    public function accept(User $actor, UserRelation $relation): bool
    {
        if (!$relation->isPending() || !$this->canRepresentParty($actor, $relation->getRecipientParty())) {
            return false;
        }

        foreach ($this->getRelationsByParties($relation->getSourceParty(), $relation->getRecipientParty()) as $existingRelation) {
            if (!$existingRelation->isPending() && $existingRelation->getId() !== $relation->getId()) {
                $this->userRelationRepository->delete($existingRelation);
            }
        }

        $relation->setDate(time());
        $this->userRelationRepository->save($relation);

        $text = $this->getRelationConclusionText($relation);
        $this->sendMessageToParty($relation->getSourceParty(), $text);
        $this->addHistory($relation, $actor->getId(), $text);

        return true;
    }

    #[\Override]
    public function cancel(User $actor, UserRelation $relation): bool
    {
        if ($relation->isWar()) {
            return false;
        }

        $source = $relation->getSourceParty();
        $recipient = $relation->getRecipientParty();

        if ($relation->isPending()) {
            if (!$this->canRepresentParty($actor, $source)) {
                return false;
            }

            $this->userRelationRepository->delete($relation);
            $this->sendMessageToParty(
                $recipient,
                sprintf('%s hat das Angebot für ein %s zurückgezogen', $this->getPartyDescription($source), $relation->getType()->getDescription())
            );

            return true;
        }

        if (!$this->canRepresentParty($actor, $source) && !$this->canRepresentParty($actor, $recipient)) {
            return false;
        }

        $this->userRelationRepository->delete($relation);
        $counterpart = $this->canRepresentParty($actor, $source) ? $recipient : $source;
        $text = sprintf('%s hat das %s aufgelöst', $this->getPartyDescription($this->canRepresentParty($actor, $source) ? $source : $recipient), $relation->getType()->getDescription());
        $this->sendMessageToParty($counterpart, $text);
        $this->addHistory(
            $relation,
            $actor->getId(),
            sprintf(
                'Das %s zwischen %s und %s wurde aufgelöst',
                $relation->getType()->getDescription(),
                $this->getPartyDescription($source),
                $this->getPartyDescription($recipient)
            )
        );

        return true;
    }

    #[\Override]
    public function decline(User $actor, UserRelation $relation): bool
    {
        if (!$relation->isPending() || !$this->canRepresentParty($actor, $relation->getRecipientParty())) {
            return false;
        }

        $this->userRelationRepository->delete($relation);
        $this->sendMessageToParty(
            $relation->getSourceParty(),
            sprintf('%s hat das Angebot für ein %s abgelehnt', $this->getPartyDescription($relation->getRecipientParty()), $relation->getType()->getDescription())
        );

        return true;
    }

    #[\Override]
    public function suggestPeace(User $actor, UserRelation $relation): bool
    {
        if (
            !$relation->isWar()
            || $relation->isPending()
            || (!$this->canRepresentParty($actor, $relation->getSourceParty())
                && !$this->canRepresentParty($actor, $relation->getRecipientParty()))
        ) {
            return false;
        }

        $source = $this->canRepresentParty($actor, $relation->getSourceParty())
            ? $relation->getSourceParty()
            : $relation->getRecipientParty();
        $recipient = $source === $relation->getSourceParty()
            ? $relation->getRecipientParty()
            : $relation->getSourceParty();

        foreach ($this->getRelationsByParties($source, $recipient) as $existingRelation) {
            if ($existingRelation->getType() === AllianceRelationTypeEnum::PEACE) {
                return false;
            }
        }

        $this->createRelation($source, $recipient, AllianceRelationTypeEnum::PEACE);
        $this->sendMessageToParty(
            $recipient,
            sprintf('%s hat %s ein Friedensabkommen angeboten', $this->getPartyDescription($source), $this->getPartyDescription($recipient))
        );

        return true;
    }

    #[\Override]
    public function removeRelationsForAllianceEntry(User $user, Alliance $alliance, bool $isAllianceCreation = false): void
    {
        foreach ($this->userRelationRepository->getByUserAndAlliance($user, null) as $relation) {
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
                $this->getPartyDescription($counterpart)
            );

            $this->sendMessageToParty($user, $text);
            $this->sendMessageToParty($counterpart, $text);
            $this->userRelationRepository->delete($relation);
        }
    }

    private function canCreateForParty(User $actor, User|Alliance $party): bool
    {
        if ($party instanceof User) {
            return $actor->getAlliance() === null && $actor->getId() === $party->getId();
        }

        return $actor->getAlliance()?->getId() === $party->getId()
            && $this->canCreateForAlliance($actor, $party);
    }

    private function canRepresentParty(User $actor, User|Alliance $party): bool
    {
        if ($party instanceof User) {
            return $actor->getAlliance() === null && $actor->getId() === $party->getId();
        }

        return $actor->getAlliance()?->getId() === $party->getId()
            && $this->canManageForAlliance($actor, $party);
    }

    private function canCreateForAlliance(User $user, Alliance $alliance): bool
    {
        return $this->allianceJobManager->hasUserPermission($user, $alliance, AllianceJobPermissionEnum::CREATE_AGREEMENTS);
    }

    private function canManageForAlliance(User $user, Alliance $alliance): bool
    {
        return $this->canCreateForAlliance($user, $alliance)
            || $this->allianceJobManager->hasUserPermission($user, $alliance, AllianceJobPermissionEnum::DIPLOMATIC)
            || $this->allianceJobManager->hasUserPermission($user, $alliance, AllianceJobPermissionEnum::EDIT_DIPLOMATIC_DOCUMENTS);
    }

    private function hasValidParties(User|Alliance $source, User|Alliance $recipient): bool
    {
        if ($source instanceof User && $recipient instanceof User) {
            return $source->getId() !== $recipient->getId()
                && $source->getAlliance() === null
                && $recipient->getAlliance() === null;
        }

        if ($source instanceof Alliance && $recipient instanceof User) {
            return $recipient->getAlliance() === null;
        }

        if ($source instanceof User) {
            return $source->getAlliance() === null;
        }

        return false;
    }

    private function createRelation(User|Alliance $source, User|Alliance $recipient, AllianceRelationTypeEnum $type, int $date = 0): UserRelation
    {
        $relation = $this->userRelationRepository->prototype()->setType($type)->setDate($date);

        if ($source instanceof User) {
            $relation->setSourceUser($source);
        } else {
            $relation->setSourceAlliance($source);
        }

        if ($recipient instanceof User) {
            $relation->setRecipientUser($recipient);
        } else {
            $relation->setRecipientAlliance($recipient);
        }

        $this->userRelationRepository->save($relation);

        return $relation;
    }

    /**
     * @return list<UserRelation>
     */
    private function getRelationsByParties(User|Alliance $source, User|Alliance $recipient): array
    {
        if ($source instanceof User && $recipient instanceof User) {
            return $this->userRelationRepository->getByUserPair($source, $recipient);
        }
        if ($source instanceof Alliance && $recipient instanceof Alliance) {
            throw new InvalidArgumentException('Relations between alliances are not allowed');
        }

        $alliance = $source instanceof Alliance ? $source : $recipient;
        $user = $source instanceof User ? $source : $recipient;

        return $this->userRelationRepository->getByAllianceAndUserPair($alliance, $user);
    }

    private function getPartyDescription(User|Alliance $party): string
    {
        return $party instanceof Alliance
            ? sprintf('Die Allianz %s', $party->getName())
            : sprintf('Der Siedler %s', $party->getName());
    }

    private function sendMessageToParty(User|Alliance $party, string $text): void
    {
        if ($party instanceof Alliance) {
            $this->allianceActionManager->sendMessage($party->getId(), $text);
            return;
        }

        $this->privateMessageSender->send(UserConstants::USER_NOONE, $party->getId(), $text);
    }

    private function getRelationConclusionText(UserRelation $relation): string
    {
        $source = $relation->getSourceParty();
        $recipient = $relation->getRecipientParty();

        if ($relation->getType() === AllianceRelationTypeEnum::VASSAL) {
            return sprintf('%s ist nun Vasall von %s', $this->getPartyDescription($recipient), $this->getPartyDescription($source));
        }

        return sprintf(
            '%s und %s sind ein %s eingegangen',
            $this->getPartyDescription($source),
            $this->getPartyDescription($recipient),
            $relation->getType()->getDescription()
        );
    }

    private function addHistory(UserRelation $relation, int $sourceUserId, string $text): void
    {
        $targetUser = $relation->getRecipientUser() ?? $relation->getSourceUser();
        if ($targetUser === null) {
            return;
        }

        $this->entryCreator->createEntry(
            HistoryTypeEnum::ALLIANCE,
            $text,
            $sourceUserId,
            $targetUser->getId()
        );
    }
}
