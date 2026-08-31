<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Stu\IntegrationTestCase;
use Stu\Orm\Entity\User;

class UserRepositoryTest extends IntegrationTestCase
{
    public function testLockUsersForUpdateWorksWithStableUserOrderInTransactions(): void
    {
        $dic = $this->getContainer();
        $entityManager = $dic->get(EntityManagerInterface::class);
        $userRepository = $dic->get(UserRepositoryInterface::class);
        $factionRepository = $dic->get(FactionRepositoryInterface::class);
        $faction = $factionRepository->getByChooseable(true)[0];

        $firstUser = $userRepository->prototype()
            ->setUsername('deadlock-first-' . uniqid('', true))
            ->setFaction($faction);
        $secondUser = $userRepository->prototype()
            ->setUsername('deadlock-second-' . uniqid('', true))
            ->setFaction($faction);

        $userRepository->save($firstUser);
        $userRepository->save($secondUser);
        $entityManager->flush();

        $lowId = min($firstUser->getId(), $secondUser->getId());
        $highId = max($firstUser->getId(), $secondUser->getId());

        try {
            foreach ([[ $highId, $lowId ], [ $lowId, $highId ]] as $orderedUserIds) {
                $entityManager->wrapInTransaction(function (EntityManagerInterface $entityManager) use ($userRepository, $orderedUserIds): void {
                    $userRepository->lockUsersForUpdate($orderedUserIds);

                    $lockedUsers = $entityManager->getRepository(User::class)->findBy(['id' => $orderedUserIds]);
                    static::assertCount(2, $lockedUsers);
                });
            }
        } finally {
            $userRepository->delete($firstUser);
            $userRepository->delete($secondUser);
            $entityManager->flush();
        }
    }
}
