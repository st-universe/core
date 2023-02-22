<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Stu\Orm\Entity\RpgPlotMemberInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\RpgPlotMemberRepositoryInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

/**
 * Updates the owner of a rpg-plot if the owning user gets deleted
 */
final class RpgPlotDeletionHandler implements PlayerDeletionHandlerInterface
{
    private RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository;

    private RpgPlotRepositoryInterface $rpgPlotRepository;

    private UserRepositoryInterface $userRepository;

    public function __construct(
        RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository,
        RpgPlotRepositoryInterface $rpgPlotRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->rpgPlotMemberRepository = $rpgPlotMemberRepository;
        $this->rpgPlotRepository = $rpgPlotRepository;
        $this->userRepository = $userRepository;
    }

    public function delete(UserInterface $user): void
    {
        $gameFallbackUser = $this->userRepository->getFallbackUser();
        $userId = $user->getId();

        foreach ($this->rpgPlotRepository->getByFoundingUser($userId) as $obj) {
            $item = $this->rpgPlotMemberRepository->getByPlotAndUser($obj->getId(), $userId);
            if ($item !== null) {
                $this->rpgPlotMemberRepository->delete($item);
            }

            $members = $obj->getMembers();

            if ($members->count() > 0) {
                /** @var RpgPlotMemberInterface $member */
                $member = $members->current();
                $obj->setUser($member->getUser());
            } else {
                $obj->setUser($gameFallbackUser);
            }

            $this->rpgPlotRepository->save($obj);
        }
    }
}
