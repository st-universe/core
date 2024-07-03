<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Module\Message\Lib\ContactListModeEnum;
use Stu\Orm\Entity\Contact;
use Stu\Orm\Entity\ContactInterface;

/**
 * @extends EntityRepository<Contact>
 */
final class ContactRepository extends EntityRepository implements ContactRepositoryInterface
{
    #[Override]
    public function prototype(): ContactInterface
    {
        return new Contact();
    }

    #[Override]
    public function save(ContactInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    #[Override]
    public function delete(ContactInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush();
    }

    #[Override]
    public function getByUserAndOpponent(int $userId, int $opponentId): ?ContactInterface
    {
        return $this->findOneBy([
            'user_id' => $userId,
            'recipient' => $opponentId,
        ]);
    }

    #[Override]
    public function getOrderedByUser(int $userId): array
    {
        return $this->findBy(
            ['user_id' => $userId],
            ['recipient' => 'asc'],
        );
    }

    #[Override]
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

    #[Override]
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

    #[Override]
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

    #[Override]
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
