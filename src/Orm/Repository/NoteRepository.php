<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\Note;
use Stu\Orm\Entity\NoteInterface;

/**
 * @extends EntityRepository<Note>
 */
final class NoteRepository extends EntityRepository implements NoteRepositoryInterface
{
    public function getByUserId(int $userId): array
    {
        return $this->findBy([
            'user_id' => $userId,
        ]);
    }

    public function prototype(): NoteInterface
    {
        return new Note();
    }

    public function save(NoteInterface $note): void
    {
        $em = $this->getEntityManager();

        $em->persist($note);
    }

    public function delete(NoteInterface $note): void
    {
        $em = $this->getEntityManager();

        $em->remove($note);
        $em->flush();
    }

    public function truncateByUserId(int $userId): void
    {
        $q = $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s t WHERE t.user_id = :userId',
                Note::class
            )
        );
        $q->setParameter('userId', $userId);
        $q->execute();
    }
}
