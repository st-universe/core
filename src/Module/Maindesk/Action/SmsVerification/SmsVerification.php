<?php

declare(strict_types=1);

namespace Stu\Module\Maindesk\Action\SmsVerification;

use request;
use Stu\Lib\AccountNotVerifiedException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameController;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Repository\UserRepositoryInterface;

final class SmsVerification implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SMS_VERIFICATION';

    private UserRepositoryInterface $userRepository;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        UserRepositoryInterface $userRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->userRepository = $userRepository;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        if ($user->getState() !== UserEnum::USER_STATE_SMS_VERIFICATION) {
            $this->loggerUtil->log('W');
            return;
        }

        $smsCode = request::postStringFatal('smscode');
        if ($smsCode !== $user->getSmsCode()) {
            $this->loggerUtil->log('X');
            throw new AccountNotVerifiedException('Code ungültig, bitte erneut versuchen');
        }
        $this->loggerUtil->log('Y');

        $user->setActive(UserEnum::USER_STATE_UNCOLONIZED);
        $user->setMobile(sha1($user->getMobile()));
        $this->userRepository->save($user);

        $this->loggerUtil->log('Z');

        $game->setTemplateVar(
            'DISPLAY_FIRST_COLONY_DIALOGUE',
            $user->getState() === UserEnum::USER_STATE_UNCOLONIZED
        );

        $game->setView(GameController::DEFAULT_VIEW);

        $game->addInformation('Dein Account wurde erfolgreich freigeschaltet');
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
