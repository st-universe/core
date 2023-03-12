<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Contact;
use Stu\Orm\Entity\ContactInterface;

/**
 * @extends ObjectRepository<Contact>
 *
 * @method null|ContactInterface find(integer $id)
 */
interface ContactRepositoryInterface extends ObjectRepository
{
    public function prototype(): ContactInterface;

    public function save(ContactInterface $post): void;

    public function delete(ContactInterface $post): void;

    public function getByUserAndOpponent(int $userId, int $opponentId): ?ContactInterface;

    /**
     * @return list<ContactInterface>
     */
    public function getOrderedByUser(int $userId): array;

    /**
     * @return list<ContactInterface>
     */
    public function getRemoteOrderedByUser(int $userId): array;

    public function truncateByUser(int $userId): void;

    public function truncateByUserAndOpponent(int $userId, int $opponentId): void;
}
