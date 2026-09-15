<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\OrionAuctionBidRepositoryInterface;
use Stu\Orm\Repository\OrionAuctionRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class OrionAuctionDeletionHandler implements PlayerDeletionHandlerInterface
{
    public function __construct(
        private OrionAuctionRepositoryInterface $orionAuctionRepository,
        private OrionAuctionBidRepositoryInterface $orionAuctionBidRepository,
        private CrewRepositoryInterface $crewRepository,
        private CrewAssignmentRepositoryInterface $crewAssignmentRepository,
        private UserRepositoryInterface $userRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[\Override]
    public function delete(User $user): void
    {
        foreach ($this->orionAuctionBidRepository->getByUser($user) as $bid) {
            $this->orionAuctionBidRepository->delete($bid);
        }

        $fallbackUser = $this->userRepository->find(UserConstants::USER_NOONE);
        if ($fallbackUser === null) {
            return;
        }

        foreach ($this->orionAuctionRepository->getByWinner($user) as $auction) {
            $crew = $auction->getCrew();
            $crew->setUser($fallbackUser);
            $this->crewRepository->save($crew);

            $crewAssignment = $this->crewAssignmentRepository->find($crew->getId());
            if ($crewAssignment instanceof CrewAssignment) {
                $crewAssignment->setUser($fallbackUser);
                $this->crewAssignmentRepository->save($crewAssignment);
            }

            $auction
                ->setWinnerName($user->getName())
                ->setWinner(null);
            $this->orionAuctionRepository->save($auction);
        }

        $this->entityManager->flush();
    }
}
