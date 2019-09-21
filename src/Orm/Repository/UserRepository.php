<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Module\Communication\Lib\ContactListModeEnum;
use Stu\Orm\Entity\Contact;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserInterface;

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
        $em->flush($post);
    }

    public function delete(UserInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush($post);
    }

    public function getAmountByFaction(int $factionId): int
    {
        return $this->count([
            'race' => $factionId,
        ]);
    }

    public function getByResetToken(string $resetToken): ?UserInterface
    {
        return $this->findOneBy([
            'password_token' => $resetToken,
        ]);
    }

    public function getActualPlayer(): iterable
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT u FROM %s u WHERE u.id > 100',
                User::class
            )
        )->getResult();
    }

    public function getIdlePlayer(
        int $idleTimeThreshold,
        array $ignoreIds
    ): iterable {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT u FROM %s u WHERE u.id NOT IN (:ignoreIds) AND u.lastaction < :idleTimeThreshold',
                User::class
            )
        )->setParameters([
            'idleTimeThreshold' => $idleTimeThreshold,
            'ignoreIds' => $ignoreIds
        ])->getResult();
    }

    public function getByEmail(string $email): ?UserInterface
    {
        return $this->findOneBy([
            'email' => $email
        ]);
    }

    public function getByLogin(string $loginName): ?UserInterface
    {
        return $this->findOneBy([
            'login' => $loginName
        ]);
    }

    public function getByAlliance(int $allianceId): iterable
    {
        return $this->findBy([
            'allys_id' => $allianceId
        ]);
    }

    public function getByMappingType(int $mappingType): iterable
    {
        return $this->findBy([
            'maptype' => $mappingType
        ]);
    }

    public function getList(
        string $sortField,
        string $sortOrder,
        int $limit,
        int $offset
    ): iterable {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT u FROM %s u WHERE u.id > 100 ORDER BY u.%s %s',
                User::class,
                $sortField,
                $sortOrder
            )
        )
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getResult();
    }

    public function getFriendsByUserAndAlliance(int $userId, int $allianceId): iterable
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT u FROM %s u WHERE u.id IN (
                    SELECT cl.id FROM %s cl WHERE cl.mode = 1 AND cl.recipient = :userId
                ) OR (u.allys_id IS NOT NULL AND u.allys_id = :allianceId) AND u.id != :userId
                ORDER BY u.id',
                User::class,
                Contact::class
            )
        )->setParameters([
            'userId' => $userId,
            'allianceId' => $allianceId
        ])->getResult();
    }

    public function getOrderedByLastaction(int $limit, int $ignoreUserId, int $lastActionThreshold): iterable
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT u FROM %s u WHERE u.id != :ignoreUserId AND (u.show_online_status = 1 OR u.id IN (
                        SELECT cl.user_id FROM %s cl WHERE cl.mode = :contactListModeFriend AND cl.recipient = :ignoreUserId
                    )
                ) AND u.lastaction > :lastActionThreshold',
                User::class,
                Contact::class
            )
        )->setParameters([
            'ignoreUserId' => $ignoreUserId,
            'contactListModeFriend' => ContactListModeEnum::CONTACT_FRIEND,
            'lastActionThreshold' => $lastActionThreshold
        ])
            ->setMaxResults($limit)
            ->getResult();
    }
}