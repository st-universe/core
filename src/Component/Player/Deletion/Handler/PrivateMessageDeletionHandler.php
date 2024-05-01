<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

/**
 * Updates the sending user to the fallback one on user deletion
 */
final class PrivateMessageDeletionHandler implements PlayerDeletionHandlerInterface
{
    private UserRepositoryInterface $userRepository;

    private PrivateMessageRepositoryInterface $privateMessageRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        PrivateMessageRepositoryInterface $privateMessageRepository
    ) {
        $this->userRepository = $userRepository;
        $this->privateMessageRepository = $privateMessageRepository;
    }

    public function delete(UserInterface $user): void
    {
        $this->setFallbackUserByDeletedSender($user);
        $this->unsetInboxReferenceonInbox($user);
    }

    private function setFallbackUserByDeletedSender(UserInterface $user): void
    {
        $nobody = $this->userRepository->getFallbackUser();

        foreach ($this->privateMessageRepository->getBySender($user) as $pm) {
            $pm->setSender($nobody);

            $this->privateMessageRepository->save($pm);
        }
    }

    private function unsetInboxReferenceonInbox(UserInterface $user): void
    {
        foreach ($this->privateMessageRepository->getByReceiver($user) as $pm) {

            $outboxPm = $pm->getOutboxPm();
            if ($outboxPm !== null) {
                $outboxPm->setInboxPm(null);
                $this->privateMessageRepository->save($outboxPm);
            }
        }
    }
}
