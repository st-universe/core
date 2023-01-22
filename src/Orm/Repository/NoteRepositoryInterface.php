<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Note;
use Stu\Orm\Entity\NoteInterface;

/**
 * @extends ObjectRepository<Note>
 *
 * @method null|NoteInterface find(integer $id)
 */
interface NoteRepositoryInterface extends ObjectRepository
{
    public function getByUserId(int $userId): array;

    public function prototype(): NoteInterface;

    public function save(NoteInterface $note): void;

    public function delete(NoteInterface $note): void;

    public function truncateByUserId(int $userId): void;
}
