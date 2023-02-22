<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Component\Game\GameEnum;
use Stu\Component\Game\TimeConstants;
use Stu\Module\Message\Lib\ContactListModeEnum;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\Contact;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends EntityRepository<User>
 */
final class UserRepository extends EntityRepository implements UserRepositoryInterface
{

    public function prototype(): UserInterface
    {
        return new User();
    }

    public function save(UserInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
        $em->flush();
    }

    public function delete(UserInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush();
    }

    public function getByResetToken(string $resetToken): ?UserInterface
    {
        return $this->findOneBy([
            'password_token' => $resetToken,
        ]);
    }

    public function getDeleteable(
        int $idleTimeThreshold,
        int $idleTimeVacationThreshold,
        array $ignoreIds
    ): iterable {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT u FROM %s u
                 WHERE u.id > :firstUserId
                 AND u.id NOT IN (:ignoreIds)
                 AND u.delmark != :deletionForbidden
                 AND (u.delmark = :deletionMark
                        OR (u.vac_active = false AND u.lastaction > 0 AND u.lastaction < :idleTimeThreshold)
                        OR (u.vac_active = true AND u.lastaction > 0 AND u.lastaction < :idleTimeVacationThreshold)
                    )
                 ORDER BY u.id ASC',
                User::class
            )
        )->setParameters([
            'idleTimeThreshold' => $idleTimeThreshold,
            'idleTimeVacationThreshold' => $idleTimeVacationThreshold,
            'ignoreIds' => $ignoreIds,
            'deletionMark' => UserEnum::DELETION_CONFIRMED,
            'deletionForbidden' => UserEnum::DELETION_FORBIDDEN,
            'firstUserId' => UserEnum::USER_FIRST_ID
        ])->getResult();
    }

    public function getIdleRegistrations(
        int $idleTimeThreshold
    ): iterable {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT u FROM %s u
                 WHERE (u.state = :newUser OR u.state = :smsVerification)
                 AND u.creation < :idleTimeThreshold',
                User::class
            )
        )->setParameters([
            'idleTimeThreshold' => $idleTimeThreshold,
            'newUser' => UserEnum::USER_STATE_NEW,
            'smsVerification' => UserEnum::USER_STATE_SMS_VERIFICATION
        ])->getResult();
    }

    public function getByEmail(string $email): ?UserInterface
    {
        return $this->findOneBy([
            'email' => $email
        ]);
    }

    public function getByMobile(string $mobile, string $mobileHash): ?UserInterface
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT u FROM %s u
                WHERE u.mobile = :mobile
                OR u.mobile = :mobileHash',
                User::class
            )
        )->setParameters([
            'mobile' => $mobile,
            'mobileHash' => $mobileHash
        ])->getOneOrNullResult();
    }

    public function getByLogin(string $loginName): ?UserInterface
    {
        return $this->findOneBy([
            'login' => $loginName
        ]);
    }

    public function getByAlliance(AllianceInterface $alliance): iterable
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

        return $query->setParameter('firstUserId', UserEnum::USER_FIRST_ID)->getResult();
    }

    public function getFriendsByUserAndAlliance(UserInterface $user, ?AllianceInterface $alliance): iterable
    {
        $allianceId = $alliance === null
            ? 0
            : $alliance->getId();

        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT u FROM %s u WHERE u.id IN (
                    SELECT cl.user_id FROM %s cl WHERE cl.mode = :mode AND cl.recipient = :userId
                ) OR (u.allys_id IS NOT NULL AND u.allys_id = :allianceId) AND u.id != :userId
                ORDER BY u.id',
                User::class,
                Contact::class
            )
        )->setParameters([
            'mode' => ContactListModeEnum::CONTACT_FRIEND,
            'userId' => $user->getId(),
            'allianceId' => $allianceId
        ])->getResult();
    }

    public function getOrderedByLastaction(int $limit, int $ignoreUserId, int $lastActionThreshold): iterable
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT u FROM %s u
                WHERE u.id != :ignoreUserId
                AND u.id > :firstUserId
                AND (u.show_online_status = :allowStart OR u.id IN (
                        SELECT cl.user_id FROM %s cl WHERE cl.mode = :contactListModeFriend AND cl.recipient = :ignoreUserId
                    )
                ) AND u.lastaction > :lastActionThreshold',
                User::class,
                Contact::class
            )
        )->setParameters([
            'ignoreUserId' => $ignoreUserId,
            'contactListModeFriend' => (string) ContactListModeEnum::CONTACT_FRIEND,
            'lastActionThreshold' => $lastActionThreshold,
            'allowStart' => 1,
            'firstUserId' => UserEnum::USER_FIRST_ID
        ])
            ->setMaxResults($limit)
            ->getResult();
    }

    public function getActiveAmount(): int
    {
        return (int) $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT COUNT(u.id) FROM %s u WHERE u.id >= :firstUserId',
                User::class
            )
        )->setParameter('firstUserId', UserEnum::USER_FIRST_ID)->getSingleScalarResult();
    }

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
            'firstUserId' => UserEnum::USER_FIRST_ID
        ])
            ->getSingleScalarResult();
    }

    public function getVacationAmount(): int
    {
        return (int) $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT COUNT(u.id) FROM %s u
                WHERE u.id >= :firstUserId
                AND u.vac_active = true
                AND u.vac_request_date < :vacThreshold',
                User::class
            )
        )->setParameters([
            'vacThreshold' => time() - UserEnum::VACATION_DELAY_IN_SECONDS,
            'firstUserId' => UserEnum::USER_FIRST_ID
        ])
            ->getSingleScalarResult();
    }

    public function getActiveAmountRecentlyOnline(int $threshold): int
    {
        return (int) $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT COUNT(u.id) FROM %s u WHERE u.id >= :firstUserId AND u.lastaction > :threshold',
                User::class
            )
        )->setParameters([
            'threshold' => $threshold,
            'firstUserId' => UserEnum::USER_FIRST_ID
        ])->getSingleScalarResult();
    }

    public function getNpcList(): iterable
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT u FROM %s u WHERE u.id BETWEEN 10 AND 99 ORDER BY u.id',
                User::class
            )
        )->getResult();
    }

    public function getNonNpcList(): iterable
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT u FROM %s u WHERE u.id >= :firstUserId ORDER BY u.id ASC',
                User::class
            )
        )->setParameter('firstUserId', UserEnum::USER_FIRST_ID)->getResult();
    }

    public function getFallbackUser(): UserInterface
    {
        /** @var UserInterface $user */
        $user = $this->find(GameEnum::USER_NOONE);
        /**
         * @todo The fallback user is expected to exist. for now, update the type hint.
         * Consider to throw a dedicated exception for this case
         */
        return $user;
    }
}
