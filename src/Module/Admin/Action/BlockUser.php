<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action;

use Override;
use request;
use Stu\Module\Admin\View\Playerlist\Playerlist;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuHashInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Repository\BlockedUserRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class BlockUser implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_BLOCK_USER';

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private BlockedUserRepositoryInterface $blockedUserRepository,
        private StuHashInterface $stuHash,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        //$this->loggerUtil->init('admin', LoggerEnum::LEVEL_ERROR);
        $game->setView(Playerlist::VIEW_IDENTIFIER);

        $this->loggerUtil->log('A');
        // only Admins can trigger ticks
        if (!$game->isAdmin()) {
            $game->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        $this->loggerUtil->log('B');
        $userIdToBlock = request::getIntFatal('uid');

        $userToBlock = $this->userRepository->find($userIdToBlock);
        $blockedUser = $this->blockedUserRepository->find($userIdToBlock);

        if ($userToBlock === null) {
            $game->addInformation(_('Der User konnte nicht gefunden werden!'));
            return;
        }

        if ($blockedUser !== null) {
            $this->loggerUtil->log('D');
            $game->addInformation(_('Dieser User ist bereits blockiert!'));
            return;
        }

        //setup user block
        $blockedUser = $this->blockedUserRepository->prototype();
        $blockedUser->setId($userIdToBlock);
        $blockedUser->setTime(time());
        $blockedUser->setEmailHash($this->stuHash->hash($userToBlock->getEmail()));
        if ($userToBlock->getMobile() !== null) {
            $alreadyHashed = strlen($userToBlock->getMobile()) >= 40;

            $blockedUser->setMobileHash($alreadyHashed ? $userToBlock->getMobile() : $this->stuHash->hash($userToBlock->getMobile()));
        }
        $this->blockedUserRepository->save($blockedUser);

        // mark user as deletable
        $userToBlock->setDeletionMark(UserEnum::DELETION_CONFIRMED);
        $this->userRepository->save($userToBlock);

        $this->loggerUtil->log('E');

        $game->addInformationf(_('Der Spieler %s (%d) ist nun blockiert und zur Löschung freigegeben!'), $userToBlock->getName(), $userIdToBlock);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
