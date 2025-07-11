<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\RpgPlotMemberRepositoryInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

/**
 * Updates the owner of a rpg-plot if the owning user gets deleted
 */
final class RpgPlotDeletionHandler implements PlayerDeletionHandlerInterface
{
    public function __construct(
        private RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository,
        private RpgPlotRepositoryInterface $rpgPlotRepository,
        private UserRepositoryInterface $userRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[Override]
    public function delete(User $user): void
    {
        $gameFallbackUser = $this->userRepository->getFallbackUser();
        $userId = $user->getId();

        foreach ($this->rpgPlotRepository->getByFoundingUser($userId) as $plot) {
            $members = $plot->getMembers();

            $item = $members->get($userId);
            if ($item !== null) {
                $this->rpgPlotMemberRepository->delete($item);
                $members->remove($userId);
            }

            $firstMember = $members->first();
            if ($firstMember) {
                $plot->setUser($firstMember->getUser());
            } else {
                $plot->setUser($gameFallbackUser);
            }

            $this->rpgPlotRepository->save($plot);

            $this->entityManager->flush();
            foreach ($members as $member) {
                $this->entityManager->detach($member);
            }
            $this->entityManager->detach($plot);

            $members->clear();
        }
    }
}
