<?php

// @todo fix and enable strict typing
declare(strict_types=0);

namespace Stu\Module\Communication\Lib;

use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;

class PrivateMessageSender
{
    public static function sendPM($sender, $recipient, $text, $category = PM_SPECIAL_MAIN)
    {
        if ($sender == $recipient) {
            return;
        }
        // @todo refactor
        global $container;

        $privateMessageFolderRepo = $container->get(PrivateMessageFolderRepositoryInterface::class);
        $privateMessageRepo = $container->get(PrivateMessageRepositoryInterface::class);

        $pm = $privateMessageRepo->prototype();
        $pm->setDate(time());
        $folder = $privateMessageFolderRepo->getByUserAndSpecial((int)$recipient, (int)$category);
        $pm->setCategory($folder);
        $pm->setText($text);
        $pm->setRecipientId($recipient);
        $pm->setSenderId($sender);
        if ($sender != USER_NOONE) {
            $pm->copyPM();
        }

        $privateMessageRepo->save($pm);
    }
}