<?php

declare(strict_types=1);

namespace Stu\Module\Index\Action\Login;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Lib\LoginException;
use Stu\Lib\SessionInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\PlayerSetting\Lib\PlayerEnum;
use Stu\Orm\Repository\UserRepositoryInterface;

final class Login implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_LOGIN';

    private LoginRequestInterface $loginRequest;

    private SessionInterface $session;

    private UserRepositoryInterface $userRepository;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        LoginRequestInterface $loginRequest,
        SessionInterface $session,
        UserRepositoryInterface $userRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loginRequest = $loginRequest;
        $this->session = $session;
        $this->userRepository = $userRepository;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    /**
     * @throws LoginException
     */
    public function handle(GameControllerInterface $game): void
    {
        $this->session->login(
            $this->loginRequest->getLoginName(),
            $this->loginRequest->getPassword()
        );

        $user = $this->session->getUser();

        if ($this->loginRequest->getLoginName() === 'smstest') {
            $this->loggerUtil->init('login', LoggerEnum::LEVEL_ERROR);
        }

        if ($user === null) {
            $this->loggerUtil->log('user is null');
        } else {
            $this->loggerUtil->log('user is not null');
        }

        // check for sms verification
        if ($user !== null && $user->getActive() === PlayerEnum::USER_SMS_VERIFICATION) {
            $this->loggerUtil->log('A');
            $smsCode = $this->loginRequest->getSmsVerificationCode();
            if ($smsCode === null || $smsCode !== $user->getSmsCode()) {
                $this->loggerUtil->log('B');
                throw new LoginException(_('SMS-Verifikation Code inkorrekt'));
            }

            $this->loggerUtil->log('C');
            // sms code was correct, activate user
            $user->setActive(PlayerEnum::USER_NEW);
            $this->userRepository->save($user);
        }
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
