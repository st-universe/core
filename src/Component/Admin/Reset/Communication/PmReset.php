<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset\Communication;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;

final class PmReset implements PmResetInterface
{
    private PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository;

    private EntityManagerInterface $entityManager;

    public function __construct(
        PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->privateMessageFolderRepository = $privateMessageFolderRepository;
        $this->entityManager = $entityManager;
    }

    public function resetPms(): void
    {
        $this->deleteAllPrivateMessages();
        $this->entityManager->flush();
    }

    private function deleteAllPrivateMessages(): void
    {
        echo "  - deleting all pms\n";

        foreach ($this->privateMessageFolderRepository->findAll() as $pm) {
            $this->privateMessageFolderRepository->delete($pm);
        }
    }
}
