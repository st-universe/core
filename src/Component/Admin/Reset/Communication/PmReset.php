<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset\Communication;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;

final class PmReset implements PmResetInterface
{
    private PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository;

    private ContactRepositoryInterface $contactRepository;

    private EntityManagerInterface $entityManager;

    public function __construct(
        PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository,
        ContactRepositoryInterface $contactRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->privateMessageFolderRepository = $privateMessageFolderRepository;
        $this->contactRepository = $contactRepository;
        $this->entityManager = $entityManager;
    }

    public function resetPms(): void
    {
        echo "  - deleting all pms\n";

        foreach ($this->privateMessageFolderRepository->findAll() as $pm) {
            $this->privateMessageFolderRepository->delete($pm);
        }

        $this->entityManager->flush();
    }

    public function deleteAllContacts(): void
    {
        echo "  - deleting all contacts\n";

        $this->contactRepository->truncateAllContacts();

        $this->entityManager->flush();
    }
}
