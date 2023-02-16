<?php

declare(strict_types=1);

namespace Stu\Module\Message\Lib;

use Stu\Orm\Entity\PrivateMessageFolderInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;

/**
 * Builds ui and comm related items
 */
final class PrivateMessageUiFactory implements PrivateMessageUiFactoryInterface
{
    private PrivateMessageRepositoryInterface $privateMessageRepository;

    public function __construct(
        PrivateMessageRepositoryInterface $privateMessageRepository
    ) {
        $this->privateMessageRepository = $privateMessageRepository;
    }

    public function createPrivateMessageFolderItem(
        PrivateMessageFolderInterface $privateMessageFolder
    ): PrivateMessageFolderItem {
        return new PrivateMessageFolderItem(
            $this->privateMessageRepository,
            $privateMessageFolder
        );
    }
}
