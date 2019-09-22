<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Lib;

use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class PrivateMessageSender implements PrivateMessageSenderInterface
{
    private $privateMessageFolderRepository;

    private $privateMessageRepository;

    private $userRepository;

    public function __construct(
        PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository,
        PrivateMessageRepositoryInterface $privateMessageRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->privateMessageFolderRepository = $privateMessageFolderRepository;
        $this->privateMessageRepository = $privateMessageRepository;
        $this->userRepository = $userRepository;
    }

    public function send(
        int $senderId,
        int $recipientId,
        string $text,
        int $category = PM_SPECIAL_MAIN
    ): void {
        if ($senderId == $recipientId) {
            return;
        }
        $folder = $this->privateMessageFolderRepository->getByUserAndSpecial((int)$recipientId, (int)$category);

        $pm = $this->privateMessageRepository->prototype();
        $pm->setDate(time());
        $pm->setCategory($folder);
        $pm->setText($text);
        $pm->setRecipient($this->userRepository->find($recipientId));
        $pm->setSender($this->userRepository->find($senderId));
        $pm->setNew(true);

        $this->privateMessageRepository->save($pm);

        if ($senderId != USER_NOONE) {

            $folder = $this->privateMessageFolderRepository->getByUserAndSpecial($senderId, PM_SPECIAL_PMOUT);

            $newobj = clone($pm);
            $newobj->setSender($pm->getRecipient());
            $newobj->setRecipient($pm->getSender());
            $newobj->setCategory($folder);
            $newobj->setNew(false);

            $this->privateMessageRepository->save($newobj);
        }
    }
}
