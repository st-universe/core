<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\Note;
use Stu\Orm\Entity\NoteInterface;

/**
 * @extends EntityRepository<Note>
 */
final class NoteRepository extends EntityRepository implements NoteRepositoryInterface
{
    #[Override]
    public function getByUserId(int $userId): array
    {
        return $this->findBy([
            'user_id' => $userId
        ]);
    }

    #[Override]
    public function prototype(): NoteInterface
    {
        return new Note();
    }

    #[Override]
    public function save(NoteInterface $note): void
    {
        $em = $this->getEntityManager();

        $em->persist($note);
    }

    #[Override]
    public function delete(NoteInterface $note): void
    {
        $em = $this->getEntityManager();

        $em->remove($note);
        $em->flush();
    }

    #[Override]
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

    #[Override]
    public function truncateAllNotes(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s n',
                Note::class
            )
        )->execute();
    }
}
