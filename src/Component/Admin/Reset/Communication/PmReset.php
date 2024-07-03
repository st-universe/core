<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset\Communication;

use Override;
use Doctrine\ORM\EntityManagerInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;

final class PmReset implements PmResetInterface
{
    public function __construct(private PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository, private PrivateMessageRepositoryInterface $privateMessageRepository, private ContactRepositoryInterface $contactRepository, private EntityManagerInterface $entityManager)
    {
    }

    #[Override]
    public function unsetAllInboxReferences(): void
    {
        echo "  - unset all inbox references\n";

        $this->privateMessageRepository->unsetAllInboxReferences();

        $this->entityManager->flush();
    }

    #[Override]
    public function resetAllNonNpcPmFolders(): void
    {
        echo "  - deleting all non npc pm folders\n";

        $this->privateMessageFolderRepository->truncateAllNonNpcFolders();

        $this->entityManager->flush();
    }

    #[Override]
    public function resetPms(): void
    {
        echo "  - deleting all pms\n";

        $this->privateMessageRepository->truncateAllPrivateMessages();

        $this->entityManager->flush();
    }

    #[Override]
    public function deleteAllContacts(): void
    {
        echo "  - deleting all contacts\n";

        $this->contactRepository->truncateAllContacts();

        $this->entityManager->flush();
    }
}
