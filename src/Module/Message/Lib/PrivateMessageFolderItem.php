<?php

declare(strict_types=1);

namespace Stu\Module\Message\Lib;

use Stu\Orm\Entity\PrivateMessageFolderInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;

class PrivateMessageFolderItem
{
    public function __construct(private PrivateMessageRepositoryInterface $privateMessageRepository, private PrivateMessageFolderInterface $privateMessageFolder)
    {
    }

    /**
     * Returns the id of the folder
     */
    public function getId(): int
    {
        return $this->privateMessageFolder->getId();
    }

    /**
     * Returns the name of the folder
     */
    public function getDescription(): string
    {
        return $this->privateMessageFolder->getDescription();
    }

    /**
     * Returns the amount if pms
     */
    public function getCategoryCount(): int
    {
        return $this->privateMessageRepository->getAmountByFolder(
            $this->privateMessageFolder
        );
    }

    /**
     * Returns the amount of new pms
     */
    public function getCategoryCountNew(): int
    {
        return $this->privateMessageRepository->getNewAmountByFolder(
            $this->privateMessageFolder
        );
    }

    /**
     * Returns `true` if the folder can be moved
     */
    public function isDropable(): bool
    {
        return $this->privateMessageFolder->isDropable();
    }
}
