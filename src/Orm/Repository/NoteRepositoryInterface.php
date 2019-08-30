<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\NoteInterface;

interface NoteRepositoryInterface extends ObjectRepository
{
    public function getByUserId(int $userId): array;

    public function prototype(): NoteInterface;

    public function save(NoteInterface $note): void;

    public function delete(NoteInterface $note): void;

    public function truncateByUserId(int $userId): void;
}