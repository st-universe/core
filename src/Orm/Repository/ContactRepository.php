<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Module\Message\Lib\ContactListModeEnum;
use Stu\Orm\Entity\Contact;
use Stu\Orm\Entity\User;

/**
 * @extends EntityRepository<Contact>
 */
final class ContactRepository extends EntityRepository implements ContactRepositoryInterface
{
    #[Override]
    public function prototype(): Contact
    {
        return new Contact();
    }

    #[Override]
    public function save(Contact $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    #[Override]
    public function delete(Contact $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush();
    }

    #[Override]
    public function getByUserAndOpponent(int $userId, int $opponentId): ?Contact
    {
        return $this->findOneBy([
            'user_id' => $userId,
            'recipient' => $opponentId,
        ]);
    }

    #[Override]
    public function getOrderedByUser(User $user): array
    {
        return $this->findBy(
            ['user_id' => $user->getId()],
            ['recipient' => 'asc'],
        );
    }

    #[Override]
    public function getRemoteOrderedByUser(User $user): array
    {
        return $this->findBy(
            [
                'recipient' => $user->getId(),
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
}
