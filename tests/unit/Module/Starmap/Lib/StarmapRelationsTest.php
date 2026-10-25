<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\Lib;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\TestWith;
use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Component\Alliance\Enum\RelationPermissionDirectionEnum;
use Stu\Component\Alliance\Enum\RelationPermissionEnum;
use Stu\Module\Message\Lib\ContactListModeEnum;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\Contact;
use Stu\Orm\Entity\Relation;
use Stu\Orm\Entity\RelationPermission;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\RelationRepositoryInterface;
use Stu\StuTestCase;

final class StarmapRelationsTest extends StuTestCase
{
    private ContactRepositoryInterface&MockInterface $contacts;
    private RelationRepositoryInterface&MockInterface $relations;
    private StarmapRelations $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->contacts = $this->mock(ContactRepositoryInterface::class);
        $this->relations = $this->mock(RelationRepositoryInterface::class);
        $this->subject = new StarmapRelations($this->relations, $this->contacts);
    }

    public function testContactsOnlyGrantIncomingPositionSharing(): void
    {
        $user = $this->user(1);
        $outgoing = [];
        foreach ([ContactListModeEnum::FRIEND, ContactListModeEnum::ENEMY, ContactListModeEnum::NEUTRAL] as $mode) {
            $contact = $this->mock(Contact::class);
            $contact->shouldReceive('getMode')->andReturn($mode);
            $contact->shouldReceive('getRecipientId')->andReturn($mode->value + 10);
            $outgoing[] = $contact;
        }
        $shared = $this->mock(Contact::class);
        $shared->shouldReceive('hasPermission')->with(RelationPermissionEnum::SHARE_LIVE_MAP_POSITIONS)->andReturn(true);
        $shared->shouldReceive('getUserId')->andReturn(20);
        $unshared = $this->mock(Contact::class);
        $unshared->shouldReceive('hasPermission')->with(RelationPermissionEnum::SHARE_LIVE_MAP_POSITIONS)->andReturn(false);
        $this->contacts->shouldReceive('getOrderedByUser')->with($user)->once()->andReturn($outgoing);
        $this->contacts->shouldReceive('getByRecipient')->with($user)->once()->andReturn([$shared, $unshared]);
        $this->relations->shouldReceive('getByUserAndAlliance')->with($user, null)->once()->andReturn([]);

        $expected = $this->emptyContext();
        $expected['friendlyUserIds'][11] = true;
        $expected['enemyUserIds'][12] = true;
        $expected['sharedUserIds'][20] = true;
        self::assertSame($expected, $this->subject->getRelationContext($user));
    }

    #[TestWith([false, false, false])]
    #[TestWith([false, false, true])]
    #[TestWith([false, true, false])]
    #[TestWith([false, true, true])]
    #[TestWith([true, false, false])]
    #[TestWith([true, false, true])]
    #[TestWith([true, true, false])]
    #[TestWith([true, true, true])]
    public function testWarAndMutualPermissionsApplyOnEitherSide(bool $ownAlliance, bool $otherAlliance, bool $reverse): void
    {
        [$user, $relation] = $this->prepareRelation($ownAlliance, $otherAlliance, $reverse);
        $relation->setType(AllianceRelationTypeEnum::WAR);
        foreach ([RelationPermissionEnum::FRIENDLY, RelationPermissionEnum::SHARE_LIVE_MAP_POSITIONS] as $permission) {
            $relation->addRelationPermission(new RelationPermission()->setPermission($permission)->setRelation($relation));
        }

        $expected = $this->emptyContext();
        $expected['friendlyAllianceIds'][10] = true;
        $suffix = $otherAlliance ? 'AllianceIds' : 'UserIds';
        foreach (['friendly', 'enemy', 'shared'] as $group) {
            $expected[$group . $suffix][20] = true;
        }
        self::assertSame($expected, $this->subject->getRelationContext($user));
    }

    #[TestWith([false, false, false])]
    #[TestWith([false, false, true])]
    #[TestWith([false, true, false])]
    #[TestWith([false, true, true])]
    #[TestWith([true, false, false])]
    #[TestWith([true, false, true])]
    #[TestWith([true, true, false])]
    #[TestWith([true, true, true])]
    public function testDirectedPermissionsOnlyApplyToTheRecipient(bool $ownAlliance, bool $otherAlliance, bool $reverse): void
    {
        [$user, $relation] = $this->prepareRelation($ownAlliance, $otherAlliance, $reverse);
        foreach ([RelationPermissionEnum::FRIENDLY, RelationPermissionEnum::SHARE_LIVE_MAP_POSITIONS] as $permission) {
            $relation->addRelationPermission(new RelationPermission()
                ->setPermission($permission)
                ->setDirection(RelationPermissionDirectionEnum::SOURCE_TO_RECIPIENT)
                ->setRelation($relation));
        }

        $expected = $this->emptyContext();
        $expected['friendlyAllianceIds'][10] = true;
        if ($reverse) {
            $suffix = $otherAlliance ? 'AllianceIds' : 'UserIds';
            $expected['friendly' . $suffix][20] = true;
            $expected['shared' . $suffix][20] = true;
        }
        self::assertSame($expected, $this->subject->getRelationContext($user));
    }

    public function testPendingAndUnrelatedRelationsAreIgnored(): void
    {
        $user = $this->user(1);
        $pending = new Relation()->setSourceUser($user)->setRecipientUser($this->user(2))->setType(AllianceRelationTypeEnum::WAR);
        $unrelated = new Relation()->setSourceUser($this->user(3))->setRecipientUser($this->user(4))
            ->setDate(1)->setType(AllianceRelationTypeEnum::WAR);
        $this->contacts->shouldReceive('getOrderedByUser')->with($user)->andReturn([]);
        $this->contacts->shouldReceive('getByRecipient')->with($user)->andReturn([]);
        $this->relations->shouldReceive('getByUserAndAlliance')->with($user, null)->andReturn([$pending, $unrelated]);

        self::assertSame($this->emptyContext(), $this->subject->getRelationContext($user));
    }

    public function testPendingAndRevokedPermissionsDoNotGrantAccess(): void
    {
        [$user, $relation] = $this->prepareRelation(false, false, false);
        $relation->addRelationPermission(new RelationPermission()->setPermission(RelationPermissionEnum::FRIENDLY)
            ->setRelation($relation)->setPending(true));
        $relation->addRelationPermission(new RelationPermission()->setPermission(RelationPermissionEnum::SHARE_LIVE_MAP_POSITIONS)
            ->setRelation($relation)->setGranted(false));
        $expected = $this->emptyContext();
        $expected['friendlyAllianceIds'][10] = true;

        self::assertSame($expected, $this->subject->getRelationContext($user));
    }

    #[TestWith([1, null, false, [], [true, true, false, true, false]])]
    #[TestWith([2, 10, false, [], [false, true, false, false, false]])]
    #[TestWith([2, 10, true, [], [false, true, false, true, false]])]
    #[TestWith([2, null, true, [], [false, false, false, false, false]])]
    #[TestWith([2, 20, true, [], [false, false, false, false, false]])]
    #[TestWith([2, null, false, ['friendlyUserIds'], [false, true, false, false, false]])]
    #[TestWith([2, 20, false, ['friendlyAllianceIds'], [false, true, false, false, false]])]
    #[TestWith([2, null, false, ['enemyUserIds'], [false, false, true, false, false]])]
    #[TestWith([2, 20, false, ['enemyAllianceIds'], [false, false, true, false, false]])]
    #[TestWith([2, 20, false, ['friendlyUserIds', 'enemyAllianceIds'], [false, true, false, false, false]])]
    #[TestWith([2, 20, false, ['enemyUserIds', 'friendlyAllianceIds'], [false, true, false, false, false]])]
    #[TestWith([2, null, false, ['sharedUserIds'], [false, false, false, false, true]])]
    #[TestWith([2, 20, false, ['sharedAllianceIds'], [false, false, false, false, true]])]
    public function testSpacecraftAccessAndFriendshipPrecedence(int $ownerId, ?int $allianceId, bool $canSeeAllianceShips, array $groups, array $expected): void
    {
        $user = $this->user(1, $this->alliance(10));
        $context = $this->emptyContext();
        foreach ($groups as $group) {
            $context[$group][str_contains($group, 'Alliance') ? 20 : 2] = true;
        }

        $actual = $this->subject->getSpacecraftRelationship(
            ['user_id' => $ownerId, 'alliance_id' => $allianceId],
            $user,
            $canSeeAllianceShips,
            $context
        );

        self::assertSame(array_combine(['isOwn', 'isFriendly', 'isEnemy', 'hasDetails', 'hasSharedLiveMapPosition'], $expected), $actual);
    }

    private function prepareRelation(bool $ownAlliance, bool $otherAlliance, bool $reverse): array
    {
        $alliance = $this->alliance(10);
        $user = $this->user(1, $alliance);
        $own = $ownAlliance ? $alliance : $user;
        $other = $otherAlliance ? $this->alliance(20) : $this->user(20);
        $source = $reverse ? $other : $own;
        $recipient = $reverse ? $own : $other;
        $relation = new Relation();
        $source instanceof User ? $relation->setSourceUser($source) : $relation->setSourceAlliance($source);
        $recipient instanceof User ? $relation->setRecipientUser($recipient) : $relation->setRecipientAlliance($recipient);
        $relation->setDate(1);
        $this->contacts->shouldReceive('getOrderedByUser')->with($user)->andReturn([]);
        $this->contacts->shouldReceive('getByRecipient')->with($user)->andReturn([]);
        $this->relations->shouldReceive('getByUserAndAlliance')->with($user, $alliance)->once()
            ->andReturn($ownAlliance && $otherAlliance ? [] : [$relation]);
        $this->relations->shouldReceive('getActiveByAlliance')->with(10)->once()
            ->andReturn($ownAlliance && $otherAlliance ? [$relation] : []);

        return [$user, $relation];
    }

    private function user(int $id, ?Alliance $alliance = null): User
    {
        $user = $this->mock(User::class);
        $user->shouldReceive('getId')->andReturn($id);
        $user->shouldReceive('getAlliance')->andReturn($alliance);

        return $user;
    }

    private function alliance(int $id): Alliance
    {
        $alliance = $this->mock(Alliance::class);
        $alliance->shouldReceive('getId')->andReturn($id);

        return $alliance;
    }

    private function emptyContext(): array
    {
        return [
            'friendlyUserIds' => [],
            'enemyUserIds' => [],
            'friendlyAllianceIds' => [],
            'enemyAllianceIds' => [],
            'sharedUserIds' => [],
            'sharedAllianceIds' => []
        ];
    }
}
