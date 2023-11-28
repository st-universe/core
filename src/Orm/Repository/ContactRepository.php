<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Module\Message\Lib\ContactListModeEnum;
use Stu\Orm\Entity\Contact;
use Stu\Orm\Entity\ContactInterface;

/**
 * @extends EntityRepository<Contact>
 */
final class ContactRepository extends EntityRepository implements ContactRepositoryInterface
{
    public function prototype(): ContactInterface
    {
        return new Contact();
    }

    public function save(ContactInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    public function delete(ContactInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush();
    }

    public function getByUserAndOpponent(int $userId, int $opponentId): ?ContactInterface
    {
        return $this->findOneBy([
            'user_id' => $userId,
            'recipient' => $opponentId,
        ]);
    }

    public function getOrderedByUser(int $userId): array
    {
        return $this->findBy(
            ['user_id' => $userId],
            ['recipient' => 'asc'],
        );
    }

    public function getRemoteOrderedByUser(int $userId): array
    {
        return $this->findBy(
            [
                'recipient' => $userId,
                'mode' => [ContactListModeEnum::FRIEND->value, ContactListModeEnum::ENEMY->value],
            ],
            [
                'mode' => 'asc',
                'user_id' => 'asc',
            ],
        );
    }

    public function truncateByUser(int $userId): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s c WHERE c.user_id = :userId',
                Contact::class
            ),
        )->setParameters([
            'userId' => $userId
        ])->execute();
    }

    public function truncateByUserAndOpponent(int $userId, int $opponentId): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s c WHERE c.user_id = :userId OR c.recipient = :opponentId',
                Contact::class
            ),
        )->setParameters([
            'userId' => $userId,
            'opponentId' => $opponentId
        ])->execute();
    }

    public function truncateAllContacts(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s c',
                Contact::class
            )
        )->execute();
    }
}
