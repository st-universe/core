<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Component\Game\TimeConstants;
use Stu\Exception\FallbackUserDoesNotExistException;
use Stu\Module\Message\Lib\ContactListModeEnum;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\PlayerSetting\Lib\UserSettingEnum;
use Stu\Module\PlayerSetting\Lib\UserStateEnum;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\Contact;
use Stu\Orm\Entity\TradeLicense;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserRegistration;
use Stu\Orm\Entity\UserSetting;

/**
 * @extends EntityRepository<User>
 */
final class UserRepository extends EntityRepository implements UserRepositoryInterface
{
    #[\Override]
    public function prototype(): User
    {
        $user = new User();
        $user->setRegistration(new UserRegistration($user));

        return $user;
    }

    #[\Override]
    public function save(User $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    #[\Override]
    public function delete(User $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
    }

    #[\Override]
    public function getByResetToken(string $resetToken): ?User
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT u FROM %s u
                    JOIN %s ur
                    WITH u = ur.user
                    WHERE ur.password_token = :token',
                    User::class,
                    UserRegistration::class
                )
            )
            ->setParameter('token', $resetToken)
            ->getOneOrNullResult();
    }

    #[\Override]
    public function getDeleteable(
        int $idleTimeThreshold,
        int $idleTimeVacationThreshold,
        array $ignoreIds
    ): array {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT u FROM %s u INDEX BY u.id
                JOIN %s ur
                WITH u = ur.user
                 WHERE u.id > :firstUserId
                 AND u.id NOT IN (:ignoreIds)
                 AND ur.delmark != :deletionForbidden
                 AND (ur.delmark = :deletionMark
                        OR (u.vac_active = :false AND u.lastaction > 0 AND u.lastaction < :idleTimeThreshold)
                        OR (u.vac_active = :true AND u.lastaction > 0 AND u.lastaction < :idleTimeVacationThreshold)
                    )
                 ORDER BY u.id ASC',
                User::class,
                UserRegistration::class
            )
        )->setParameters([
            'idleTimeThreshold' => $idleTimeThreshold,
            'idleTimeVacationThreshold' => $idleTimeVacationThreshold,
            'ignoreIds' => $ignoreIds,
            'deletionMark' => UserConstants::DELETION_CONFIRMED,
            'deletionForbidden' => UserConstants::DELETION_FORBIDDEN,
            'firstUserId' => UserConstants::USER_FIRST_ID,
            'false' => false,
            'true' => true
        ])->getResult();
    }

    #[\Override]
    public function getIdleRegistrations(
        int $idleTimeThreshold
    ): array {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT u FROM %s u INDEX BY u.id
                JOIN %s ur
                WITH u = ur.user
                 WHERE (u.state = :newUser OR u.state = :accountVerification)
                 AND ur.creation < :idleTimeThreshold',
                User::class,
                UserRegistration::class
            )
        )->setParameters([
            'idleTimeThreshold' => $idleTimeThreshold,
            'newUser' => UserStateEnum::NEW->value,
            'accountVerification' => UserStateEnum::ACCOUNT_VERIFICATION->value
        ])->getResult();
    }

    #[\Override]
    public function getByEmail(string $email): ?User
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT u FROM %s u
                    JOIN %s ur
                    WITH u = ur.user
                    WHERE ur.email = :email',
                    User::class,
                    UserRegistration::class
                )
            )
            ->setParameter('email', $email)
            ->getOneOrNullResult();
    }

    #[\Override]
    public function getByMobile(string $mobile, string $mobileHash): ?User
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT u FROM %s u
                    JOIN %s ur
                    WITH u = ur.user
                    WHERE ur.mobile = :mobile
                    OR ur.mobile = :mobileHash',
                User::class,
                UserRegistration::class
            )
        )->setParameters([
            'mobile' => $mobile,
            'mobileHash' => $mobileHash
        ])->getOneOrNullResult();
    }

    #[\Override]
    public function getByLogin(string $loginName): ?User
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT u FROM %s u
                        JOIN %s ur
                        WITH u = ur.user
                        WHERE ur.login = :login',
                    User::class,
                    UserRegistration::class
                )
            )
            ->setParameter('login', $loginName)
            ->getOneOrNullResult();
    }

    #[\Override]
    public function getByAlliance(Alliance $alliance): array
    {
        return $this->findBy(
            [
                'alliance' => $alliance
            ],
            [
                'id' => 'ASC'
            ]
        );
    }

    #[\Override]
    public function getList(
        string $sortField,
        string $sortOrder,
        ?int $limit,
        int $offset
    ): array {
        $query = $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT u FROM %s u WHERE u.id >= :firstUserId ORDER BY u.%s %s',
                    User::class,
                    $sortField,
                    $sortOrder
                )
            )
            ->setFirstResult($offset);

        if ($limit !== null) {
            $query->setMaxResults($limit);
        }

        return $query->setParameter('firstUserId', UserConstants::USER_FIRST_ID)->getResult();
    }

    #[\Override]
    public function getNPCAdminList(
        string $sortField,
        string $sortOrder,
        ?int $limit,
        int $offset
    ): array {
        $query = $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT u FROM %s u WHERE u.id IN (10, 11, 12, 13, 14, 15, 16, 17, 19, 20, 101, 102) ORDER BY u.%s %s',
                    User::class,
                    $sortField,
                    $sortOrder
                )
            )
            ->setFirstResult($offset);

        if ($limit !== null) {
            $query->setMaxResults($limit);
        }

        return $query->getResult();
    }

    #[\Override]
    public function getFriendsByUserAndAlliance(User $user, ?Alliance $alliance): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT u FROM %s u WHERE u.id IN (
                    SELECT cl.user_id FROM %s cl WHERE cl.mode = :mode AND cl.recipient = :userId
                ) OR (u.alliance IS NOT NULL AND u.alliance = :alliance) AND u.id != :userId
                ORDER BY u.id',
                User::class,
                Contact::class
            )
        )->setParameters([
            'mode' => ContactListModeEnum::FRIEND->value,
            'userId' => $user->getId(),
            'alliance' => $alliance
        ])->getResult();
    }

    #[\Override]
    public function getOrderedByLastaction(int $limit, int $ignoreUserId, int $lastActionThreshold): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT u FROM %s u
                WHERE u.id != :ignoreUserId
                AND u.id > :firstUserId
                AND (EXISTS (SELECT us
                            FROM %s us
                            WHERE us.user = u
                            AND us.setting = :showOnlineStateSetting
                            AND us.value = :showOnlineState)
                    OR u.id IN (
                        SELECT cl.user_id FROM %s cl WHERE cl.mode = :contactListModeFriend AND cl.recipient = :ignoreUserId
                    )
                ) AND u.lastaction > :lastActionThreshold',
                User::class,
                UserSetting::class,
                Contact::class
            )
        )->setParameters([
            'ignoreUserId' => $ignoreUserId,
            'contactListModeFriend' => ContactListModeEnum::FRIEND->value,
            'lastActionThreshold' => $lastActionThreshold,
            'showOnlineStateSetting' => UserSettingEnum::SHOW_ONLINE_STATUS->value,
            'showOnlineState' => 1,
            'firstUserId' => UserConstants::USER_FIRST_ID
        ])
            ->setMaxResults($limit)
            ->getResult();
    }

    #[\Override]
    public function getActiveAmount(): int
    {
        return (int) $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT COUNT(u.id) FROM %s u WHERE u.id >= :firstUserId',
                User::class
            )
        )->setParameter('firstUserId', UserConstants::USER_FIRST_ID)->getSingleScalarResult();
    }

    #[\Override]
    public function getInactiveAmount(int $days): int
    {
        return (int) $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT COUNT(u.id) FROM %s u
                WHERE u.id >= :firstUserId
                AND u.lastaction < :threshold',
                User::class
            )
        )->setParameters([
            'threshold' => time() - $days * TimeConstants::ONE_DAY_IN_SECONDS,
            'firstUserId' => UserConstants::USER_FIRST_ID
        ])
            ->getSingleScalarResult();
    }

    #[\Override]
    public function getVacationAmount(): int
    {
        return (int) $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT COUNT(u.id) FROM %s u
                WHERE u.id >= :firstUserId
                AND u.vac_active = :true
                AND u.vac_request_date < :vacThreshold',
                User::class
            )
        )->setParameters([
            'vacThreshold' => time() - UserConstants::VACATION_DELAY_IN_SECONDS,
            'firstUserId' => UserConstants::USER_FIRST_ID,
            'true' => true
        ])
            ->getSingleScalarResult();
    }

    #[\Override]
    public function getActiveAmountRecentlyOnline(int $threshold): int
    {
        return (int) $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT COUNT(u.id) FROM %s u WHERE u.id >= :firstUserId AND u.lastaction > :threshold',
                User::class
            )
        )->setParameters([
            'threshold' => $threshold,
            'firstUserId' => UserConstants::USER_FIRST_ID
        ])->getSingleScalarResult();
    }

    #[\Override]
    public function getNpcList(): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT u FROM %s u
                WHERE u.id BETWEEN 10 AND :firstUserId - 1
                ORDER BY u.id',
                User::class
            )
        )->setParameter('firstUserId', UserConstants::USER_FIRST_ID)
            ->getResult();
    }

    #[\Override]
    public function getNonNpcList(): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT u FROM %s u WHERE u.id >= :firstUserId ORDER BY u.id ASC',
                User::class
            )
        )->setParameter('firstUserId', UserConstants::USER_FIRST_ID)->getResult();
    }

    #[\Override]

    public function getNonNpcListbyFaction(int $factionid): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT u FROM %s u WHERE u.id >= :firstUserId AND u.faction_id = :factionid ORDER BY u.id ASC',
                User::class
            )
        )->setParameters(
            [
                'firstUserId' => UserConstants::USER_FIRST_ID,
                'factionid' => $factionid
            ]
        )->getResult();
    }

    #[\Override]
    public function getFallbackUser(): User
    {
        $user = $this->find(UserConstants::USER_NOONE);

        if ($user === null) {
            throw new FallbackUserDoesNotExistException(sprintf('the user with id %d does not exist', UserConstants::USER_NOONE));
        }

        return $user;
    }

    #[\Override]
    public function getUsersWithActiveLicense(int $tradePostId, int $currentTime, ?int $factionId = null): array
    {
        $qb = $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT u FROM %s u
                JOIN %s tl WITH tl.user = u
                WHERE tl.posts_id = :tradePostId
                AND tl.expired >= :currentTime
                AND u.id >= :firstUserId',
                User::class,
                TradeLicense::class
            )
        )
            ->setParameters([
                'tradePostId' => $tradePostId,
                'currentTime' => $currentTime,
                'firstUserId' => UserConstants::USER_FIRST_ID
            ]);

        if ($factionId !== null) {
            $qb = $this->getEntityManager()->createQuery(
                sprintf(
                    'SELECT u FROM %s u
                    JOIN %s tl WITH tl.user = u
                    WHERE tl.posts_id = :tradePostId
                    AND tl.expired >= :currentTime
                    AND u.faction_id = :factionId
                    AND u.id >= :firstUserId',
                    User::class,
                    TradeLicense::class
                )
            )
                ->setParameters([
                    'tradePostId' => $tradePostId,
                    'currentTime' => $currentTime,
                    'factionId' => $factionId,
                    'firstUserId' => UserConstants::USER_FIRST_ID
                ]);
        }

        return $qb->getResult();
    }
}
