<?php

declare(strict_types=1);

namespace Stu\Module\Message\Lib;

use Stu\Orm\Entity\PrivateMessageFolderInterface;

interface PrivateMessageUiFactoryInterface
{
    public function createPrivateMessageFolderItem(PrivateMessageFolderInterface $privateMessageFolder): PrivateMessageFolderItem;
}
