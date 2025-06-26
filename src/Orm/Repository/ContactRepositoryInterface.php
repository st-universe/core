<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Contact;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<Contact>
 *
 * @method null|Contact find(integer $id)
 */
interface ContactRepositoryInterface extends ObjectRepository
{
    public function prototype(): Contact;

    public function save(Contact $post): void;

    public function delete(Contact $post): void;

    public function getByUserAndOpponent(int $userId, int $opponentId): ?Contact;

    /**
     * @return list<Contact>
     */
    public function getOrderedByUser(User $user): array;

    /**
     * @return list<Contact>
     */
    public function getRemoteOrderedByUser(User $user): array;

    public function truncateByUser(int $userId): void;

    public function truncateByUserAndOpponent(int $userId, int $opponentId): void;

    public function truncateAllContacts(): void;
}
