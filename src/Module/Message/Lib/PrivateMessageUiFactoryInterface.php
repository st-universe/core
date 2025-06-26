<?php

declare(strict_types=1);

namespace Stu\Module\Message\Lib;

use Stu\Orm\Entity\PrivateMessageFolder;

interface PrivateMessageUiFactoryInterface
{
    public function createPrivateMessageFolderItem(PrivateMessageFolder $privateMessageFolder): PrivateMessageFolderItem;
}
