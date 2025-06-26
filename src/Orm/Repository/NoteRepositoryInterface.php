<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Note;

/**
 * @extends ObjectRepository<Note>
 *
 * @method null|Note find(integer $id)
 */
interface NoteRepositoryInterface extends ObjectRepository
{
    /**
     * @return list<Note>
     */
    public function getByUserId(int $userId): array;

    public function prototype(): Note;

    public function save(Note $note): void;

    public function delete(Note $note): void;

    public function truncateByUserId(int $userId): void;

    public function truncateAllNotes(): void;
}
