<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Stu\Component\Game\GameEnum;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class PrivateMessageDeletionHandler implements PlayerDeletionHandlerInterface
{
    private UserRepositoryInterface $userRepository;

    private PrivateMessageRepositoryInterface $privateMessageRepository;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        UserRepositoryInterface $userRepository,
        PrivateMessageRepositoryInterface $privateMessageRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->userRepository = $userRepository;
        $this->privateMessageRepository = $privateMessageRepository;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function delete(UserInterface $user): void
    {
        //$this->loggerUtil->log('stu', LoggerEnum::LEVEL_ERROR);
        $nobody = $this->userRepository->find(GameEnum::USER_NOONE);

        foreach ($this->privateMessageRepository->getBySender($user->getId()) as $pm) {
            $pm->setSender($nobody);
            $this->privateMessageRepository->save($pm);
        }
    }
}
