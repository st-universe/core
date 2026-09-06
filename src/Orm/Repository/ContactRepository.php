<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\Contact;
use Stu\Orm\Entity\RelationPermission;
use Stu\Orm\Entity\User;

/**
 * @extends EntityRepository<Contact>
 */
final class ContactRepository extends EntityRepository implements ContactRepositoryInterface
{
    #[\Override]
    public function prototype(): Contact
    {
        return new Contact();
    }

    #[\Override]
    public function save(Contact $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    #[\Override]
    public function delete(Contact $post): void
    {
        $em = $this->getEntityManager();

        $this->deletePermissionsByContacts([$post]);
        $em->remove($post);
        $em->flush();
    }

    #[\Override]
    public function getByUserAndOpponent(int $userId, int $opponentId): ?Contact
    {
        return $this->findOneBy([
            'user_id' => $userId,
            'recipient' => $opponentId
        ]);
    }

    #[\Override]
    public function getOrderedByUser(User $user): array
    {
        return $this->findBy(
            ['user_id' => $user->getId()],
            ['recipient' => 'asc']
        );
    }

    #[\Override]
    public function getRemoteOrderedByUser(User $user): array
    {
        return $this->findBy(
            ['recipient' => $user->getId()],
            [
                'mode' => 'asc',
                'user_id' => 'asc'
            ]
        );
    }

    #[\Override]
    public function getByRecipient(User $user): array
    {
        return $this->findBy(['recipient' => $user->getId()]);
    }

    #[\Override]
    public function truncateByUser(int $userId): void
    {
        $this->deleteBy('c.user_id = :userId OR c.recipient = :userId', ['userId' => $userId]);
    }

    #[\Override]
    public function truncateByUserAndOpponent(int $userId, int $opponentId): void
    {
        $this->deleteBy(
            'c.user_id = :userId OR c.recipient = :opponentId',
            ['userId' => $userId, 'opponentId' => $opponentId]
        );
    }

    private function deleteBy(string $where, array $parameters): void
    {
        $contacts = $this
            ->getEntityManager()
            ->createQuery(
                sprintf('SELECT c FROM %s c WHERE %s', Contact::class, $where)
            )
            ->setParameters($parameters)
            ->getResult();
        $this->deletePermissionsByContacts($contacts);

        $this
            ->getEntityManager()
            ->createQuery(
                sprintf('DELETE FROM %s c WHERE %s', Contact::class, $where)
            )
            ->setParameters($parameters)
            ->execute();
    }

    private function deletePermissionsByContacts(array $contacts): void
    {
        if ($contacts === []) {
            return;
        }

        $this
            ->getEntityManager()
            ->createQuery(
                sprintf('DELETE FROM %s rp WHERE rp.contact IN (:contacts)', RelationPermission::class)
            )
            ->setParameter('contacts', $contacts)
            ->execute();
    }
}
