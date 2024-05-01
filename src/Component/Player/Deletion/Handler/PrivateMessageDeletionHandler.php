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
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PrivateMessageRepositoryInterface $privateMessageRepository,
    ) {
    }

    public function delete(UserInterface $user): void
    {
        $nobody = $this->userRepository->getFallbackUser();

        $this->setFallbackUserByDeletedSender($user, $nobody);
        $this->unsetInboxReference($user, $nobody);
    }

    private function setFallbackUserByDeletedSender(UserInterface $user, UserInterface $nobody): void
    {
        foreach ($this->privateMessageRepository->getBySender($user) as $pm) {
            $pm->setSender($nobody);

            $this->privateMessageRepository->save($pm);
        }
    }

    private function unsetInboxReference(UserInterface $user, UserInterface $nobody): void
    {
        foreach ($this->privateMessageRepository->getByReceiver($user) as $pm) {

            $pm->setRecipient($nobody);
            $this->privateMessageRepository->save($pm);

            $outboxPm = $pm->getOutboxPm();
            if ($outboxPm !== null) {
                $outboxPm->setInboxPm(null);
                $this->privateMessageRepository->save($outboxPm);
            }
        }
    }
}
