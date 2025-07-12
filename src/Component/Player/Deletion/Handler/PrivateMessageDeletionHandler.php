<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\User;

/**
 * Updates the sending user to the fallback one on user deletion
 */
final class PrivateMessageDeletionHandler implements PlayerDeletionHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    #[Override]
    public function delete(User $user): void
    {
        $this->setFallbackUserByDeletedSender($user);
        $this->unsetInboxReference($user);

        $this->entityManager->flush();
    }

    private function setFallbackUserByDeletedSender(User $user): void
    {
        $this->entityManager->getConnection()->executeStatement(
            'UPDATE stu_pms SET send_user = :nobodyId
            WHERE send_user = :userId',
            [
                'nobodyId' => UserConstants::USER_NOONE,
                'userId' => $user->getId(),
            ]
        );
    }

    private function unsetInboxReference(User $user): void
    {
        $this->entityManager->getConnection()->executeStatement(
            'UPDATE stu_pms outbox SET inbox_pm_id = NULL
            WHERE EXISTS (SELECT * FROM stu_pms inbox
                        WHERE inbox.id = outbox.inbox_pm_id
                        AND inbox.recip_user = :userId)',
            [
                'userId' => $user->getId(),
            ]
        );
    }
}
