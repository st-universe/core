<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;

final class PmCategoryDeletionHandler implements PlayerDeletionHandlerInteface
{
    private $privateMessageFolderRepository;
    private $privateMessageRepository;

    public function __construct(
        PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository,
        PrivateMessageRepositoryInterface $privateMessageRepository
    ) {
        $this->privateMessageFolderRepository = $privateMessageFolderRepository;
        $this->privateMessageRepository = $privateMessageRepository;
    }

    public function delete(UserInterface $user): void
    {
        $result = $this->privateMessageFolderRepository->getOrderedByUser($user->getId());
        foreach ($result as $folder) {
            $this->privateMessageRepository->truncateByFolder($folder->getId());

            $this->privateMessageFolderRepository->delete($folder);
        }
    }
}
