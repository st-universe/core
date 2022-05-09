<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion;

use Noodlehaus\ConfigInterface;
use Stu\Component\Player\Deletion\Handler\PlayerDeletionHandlerInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class PlayerDeletion implements PlayerDeletionInterface
{
    //3 days
    public const USER_IDLE_REGISTRATION = 259200;

    //3 months
    public const USER_IDLE_TIME = 7905600;

    //6 months
    public const USER_IDLE_TIME_VACATION = 15811200;

    private UserRepositoryInterface $userRepository;

    private ConfigInterface $config;

    private array $deletionHandler;

    /**
     * @param UserRepositoryInterface $userRepository
     * @param ConfigInterface $config
     * @param PlayerDeletionHandlerInterface[] $deletionHandler
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        ConfigInterface $config,
        array $deletionHandler
    ) {
        $this->userRepository = $userRepository;
        $this->config = $config;
        $this->deletionHandler = $deletionHandler;
    }

    public function handleDeleteable(): void
    {
        //delete all accounts that have not been activated
        $list = $this->userRepository->getIdleRegistrations(
            time() - self::USER_IDLE_REGISTRATION
        );
        foreach ($list as $player) {
            $this->delete($player);
        }

        //delete all other deleatable accounts
        $list = $this->userRepository->getDeleteable(
            time() - self::USER_IDLE_TIME,
            time() - self::USER_IDLE_TIME_VACATION,
            $this->config->get('game.admins')
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
