<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion;

use Stu\Component\Player\Deletion\Handler\PlayerDeletionHandlerInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class PlayerDeletion implements PlayerDeletionInterface
{
    //3 months
    public const USER_IDLE_TIME = 7905600;

    //6 months
    public const USER_IDLE_TIME_VACATION = 15811200;

    private UserRepositoryInterface $userRepository;

    private array $deletionHandler;

    /**
     * @param UserRepositoryInterface $userRepository
     * @param PlayerDeletionHandlerInterface[] $deletionHandler
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        array $deletionHandler
    ) {
        $this->userRepository = $userRepository;
        $this->deletionHandler = $deletionHandler;
    }

    public function handleDeleteable(): void
    {
        $list = $this->userRepository->getDeleteable(
            time() - PlayerDeletion::USER_IDLE_TIME,
            time() - PlayerDeletion::USER_IDLE_TIME_VACATION,
            [101]
        );

        foreach ($list as $player) {
            $this->delete($player);
        }
    }

    public function handleReset(): void
    {
        foreach ($this->userRepository->getActualPlayer() as $player) {
            $this->delete($player);
        }
    }

    private function delete(UserInterface $user): void
    {
        array_walk(
            $this->deletionHandler,
            function (PlayerDeletionHandlerInterface $handler) use ($user): void {
                $handler->delete($user);
            }
        );
    }
}
