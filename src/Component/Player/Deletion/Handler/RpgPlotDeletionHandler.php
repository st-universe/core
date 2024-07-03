<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Override;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\RpgPlotMemberRepositoryInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

/**
 * Updates the owner of a rpg-plot if the owning user gets deleted
 */
final class RpgPlotDeletionHandler implements PlayerDeletionHandlerInterface
{
    public function __construct(private RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository, private RpgPlotRepositoryInterface $rpgPlotRepository, private UserRepositoryInterface $userRepository)
    {
    }

    #[Override]
    public function delete(UserInterface $user): void
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
        }
    }
}
