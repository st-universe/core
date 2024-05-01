<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Doctrine\ORM\EntityManagerInterface;
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
        private EntityManagerInterface $entityManager
    ) {
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
        $updated = false;

        foreach ($this->privateMessageRepository->getByReceiver($user) as $pm) {

            $outboxPm = $pm->getOutboxPm();
            if ($outboxPm !== null) {
                $outboxPm->setInboxPm(null);
                $this->privateMessageRepository->save($outboxPm);

                $updated = true;
            }
        }

        if ($updated) {
            $this->entityManager->flush();
        }
    }
}
