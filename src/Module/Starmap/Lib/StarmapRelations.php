<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\Lib;

use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Component\Alliance\Enum\RelationPermissionEnum;
use Stu\Module\Message\Lib\ContactListModeEnum;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\Contact;
use Stu\Orm\Entity\Relation;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\RelationRepositoryInterface;

/**
 * @phpstan-type RelationContext array{
 *     friendlyUserIds: array<int, true>,
 *     enemyUserIds: array<int, true>,
 *     friendlyAllianceIds: array<int, true>,
 *     enemyAllianceIds: array<int, true>,
 *     sharedUserIds: array<int, true>,
 *     sharedAllianceIds: array<int, true>
 * }
 * @phpstan-type SpacecraftRelationship array{
 *     isOwn: bool,
 *     isFriendly: bool,
 *     isEnemy: bool,
 *     hasDetails: bool,
 *     hasSharedLiveMapPosition: bool
 * }
 */
final class StarmapRelations
{
    public function __construct(
        private RelationRepositoryInterface $allianceRelationRepository,
        private ContactRepositoryInterface $contactRepository
    ) {}

    /**
     * @return RelationContext
     */
    public function getRelationContext(User $user): array
    {
        $context = [
            'friendlyUserIds' => [],
            'enemyUserIds' => [],
            'friendlyAllianceIds' => [],
            'enemyAllianceIds' => [],
            'sharedUserIds' => [],
            'sharedAllianceIds' => []
        ];

        foreach ($this->contactRepository->getOrderedByUser($user) as $contact) {
            $group = $this->getContactGroup($contact);
            if ($group !== null) {
                $context[$group][$contact->getRecipientId()] = true;
            }
        }

        foreach ($this->contactRepository->getByRecipient($user) as $contact) {
            if ($contact->hasPermission(RelationPermissionEnum::SHARE_LIVE_MAP_POSITIONS)) {
                $context['sharedUserIds'][$contact->getUserId()] = true;
            }
        }

        $alliance = $user->getAlliance();
        $relations = $this->allianceRelationRepository->getByUserAndAlliance($user, $alliance);
        if ($alliance !== null) {
            $context['friendlyAllianceIds'][$alliance->getId()] = true;
            $relations = array_merge($relations, $this->allianceRelationRepository->getActiveByAlliance($alliance->getId()));
        }

        foreach ($relations as $relation) {
            $counterpart = $this->getCounterpart($relation, $user);
            if ($counterpart === null) {
                continue;
            }

            $partyGroup = $this->getPartyGroup($counterpart);
            foreach (['friendly', 'enemy', 'shared'] as $group) {
                if ($this->hasRelation($relation, $user, $group)) {
                    $context[$group . $partyGroup][$counterpart->getId()] = true;
                }
            }
        }

        return [
            'friendlyUserIds' => $context['friendlyUserIds'],
            'enemyUserIds' => $context['enemyUserIds'],
            'friendlyAllianceIds' => $context['friendlyAllianceIds'],
            'enemyAllianceIds' => $context['enemyAllianceIds'],
            'sharedUserIds' => $context['sharedUserIds'],
            'sharedAllianceIds' => $context['sharedAllianceIds']
        ];
    }

    private function getContactGroup(Contact $contact): ?string
    {
        return match ($contact->getMode()) {
            ContactListModeEnum::FRIEND => 'friendlyUserIds',
            ContactListModeEnum::ENEMY => 'enemyUserIds',
            default => null
        };
    }

    private function getCounterpart(Relation $relation, User $user): User|Alliance|null
    {
        if ($relation->isPending()) {
            return null;
        }
        if ($relation->isSourceParty($user)) {
            return $relation->getRecipientParty();
        }
        if ($relation->isRecipientParty($user)) {
            return $relation->getSourceParty();
        }

        return null;
    }

    private function getPartyGroup(User|Alliance $party): string
    {
        return $party instanceof User ? 'UserIds' : 'AllianceIds';
    }

    private function hasRelation(Relation $relation, User $user, string $group): bool
    {
        return match ($group) {
            'enemy' => $relation->getType() === AllianceRelationTypeEnum::WAR,
            'friendly' => $relation->hasPermissionFor($user, RelationPermissionEnum::FRIENDLY),
            'shared' => $relation->hasPermissionFor($user, RelationPermissionEnum::SHARE_LIVE_MAP_POSITIONS),
            default => false
        };
    }

    /**
     * @param array<string, mixed> $row
     * @param RelationContext $relationContext
     * @return SpacecraftRelationship
     */
    public function getSpacecraftRelationship(
        array $row,
        User $user,
        bool $canSeeAllianceShips,
        array $relationContext
    ): array {
        $ownerId = (int) $row['user_id'];
        $isOwn = $ownerId === $user->getId();
        $allianceId = $row['alliance_id'] !== null ? (int) $row['alliance_id'] : null;
        $isOwnAlliance = $this->isOwnAlliance($user, $allianceId);
        $matches = [];
        foreach (['friendly', 'enemy', 'shared'] as $group) {
            $matches[$group] = isset($relationContext[$group . 'UserIds'][$ownerId])
                || $allianceId !== null && isset($relationContext[$group . 'AllianceIds'][$allianceId]);
        }
        $isFriendly = $isOwn || $isOwnAlliance || $matches['friendly'];

        return [
            'isOwn' => $isOwn,
            'isFriendly' => $isFriendly,
            'isEnemy' => !$isFriendly && $matches['enemy'],
            'hasDetails' => $this->hasDetails($isOwn, $canSeeAllianceShips, $isOwnAlliance),
            'hasSharedLiveMapPosition' => $matches['shared']
        ];
    }

    private function isOwnAlliance(User $user, ?int $allianceId): bool
    {
        $ownAllianceId = $user->getAlliance()?->getId();

        return $ownAllianceId !== null && $allianceId === $ownAllianceId;
    }

    private function hasDetails(bool $isOwn, bool $canSeeAllianceShips, bool $isOwnAlliance): bool
    {
        return $isOwn || $canSeeAllianceShips && $isOwnAlliance;
    }
}
