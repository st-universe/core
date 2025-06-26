<?php

declare(strict_types=1);

namespace Stu\Module\Message\Lib;

use Override;
use Stu\Orm\Entity\PrivateMessageFolder;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;

/**
 * Builds ui and comm related items
 */
final class PrivateMessageUiFactory implements PrivateMessageUiFactoryInterface
{
    public function __construct(private PrivateMessageRepositoryInterface $privateMessageRepository)
    {
    }

    #[Override]
    public function createPrivateMessageFolderItem(
        PrivateMessageFolder $privateMessageFolder
    ): PrivateMessageFolderItem {
        return new PrivateMessageFolderItem(
            $this->privateMessageRepository,
            $privateMessageFolder
        );
    }
}
