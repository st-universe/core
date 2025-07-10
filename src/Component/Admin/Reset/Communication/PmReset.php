<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset\Communication;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;

final class PmReset implements PmResetInterface
{
    public function __construct(
        private PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository,
        private PrivateMessageRepositoryInterface $privateMessageRepository,
        private EntityManagerInterface $entityManager
    ) {}

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
}
