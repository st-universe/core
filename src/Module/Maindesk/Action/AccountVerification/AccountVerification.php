<?php

declare(strict_types=1);

namespace Stu\Module\Maindesk\Action\AccountVerification;

use Override;
use request;
use Stu\Lib\AccountNotVerifiedException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameController;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\NoAccessCheckControllerInterface;
use Stu\Module\Control\StuHashInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Message\Lib\SendWelcomeMessageInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Trade\Lib\LotteryFacadeInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class AccountVerification implements
    ActionControllerInterface,
    NoAccessCheckControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_ACCOUNT_VERIFICATION';

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private LotteryFacadeInterface $lotteryFacade,
        private StuHashInterface $stuHash,
        private SendWelcomeMessageInterface $sendWelcomeMessage,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {

        $user = $game->getUser();

        if ($user->getState() !== UserEnum::USER_STATE_ACCOUNT_VERIFICATION) {
            $this->loggerUtil->log('User State ist nicht ACCOUNT_VERIFICATION');
            return;
        }

        $emailCode = request::postStringFatal('emailcode');
        $registration = $user->getRegistration();

        $activationData = $user->getId() . substr($registration->getLogin(), 0, 3) . substr($registration->getEmail(), 0, 3);
        $hash = hash('sha256', $activationData);
        $expectedEmailCode = strrev(substr($hash, -6));

        if ($emailCode !== $expectedEmailCode) {
            $this->loggerUtil->log('E-Mail-Code ungültig');
            throw new AccountNotVerifiedException('E-Mail-Code ungültig, bitte erneut versuchen');
        }

        if ($registration->getMobile() !== null) {
            $smsCode = request::postStringFatal('smscode');
            if ($smsCode !== $registration->getSmsCode()) {
                $this->loggerUtil->log('SMS-Code ungültig');
                throw new AccountNotVerifiedException('SMS-Code ungültig, bitte erneut versuchen');
            }
        }

        $this->loggerUtil->log('Account wird freigeschaltet');

        $user->setState(UserEnum::USER_STATE_UNCOLONIZED);
        if ($registration->getMobile() !== null) {
            $registration->setMobile($this->stuHash->hash($registration->getMobile()));
        }
        $this->userRepository->save($user);

        $this->loggerUtil->log('Account wurde freigeschaltet');

        $game->setTemplateVar(
            'DISPLAY_FIRST_COLONY_DIALOGUE',
            $user->getState() === UserEnum::USER_STATE_UNCOLONIZED
        );

        $this->lotteryFacade->createLotteryTicket($user, true);

        $this->sendWelcomeMessage->sendWelcomeMessage($user);

        $game->setView(GameController::DEFAULT_VIEW);

        $game->addInformation('Dein Account wurde erfolgreich freigeschaltet');
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
