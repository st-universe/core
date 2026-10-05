<?php

declare(strict_types=1);

namespace Stu\Module\Maindesk\Action\AccountVerification;

use request;
use Stu\Lib\AccountNotVerifiedException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\GameController;
use Stu\Module\Control\NoAccessCheckControllerInterface;
use Stu\Module\Control\StuHashInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Message\Lib\SendWelcomeMessageInterface;
use Stu\Module\PlayerSetting\Lib\UserStateEnum;
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

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {

        $user = $context->getUser();

        if ($user->getState() !== UserStateEnum::ACCOUNT_VERIFICATION) {
            $this->loggerUtil->log('User State ist nicht ACCOUNT_VERIFICATION');
            return;
        }

        $emailCode = request::postStringFatal('emailcode');
        $registration = $user->getRegistration();

        $expectedEmailCode = $registration->getEmailCode();

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

        $user->setState(UserStateEnum::UNCOLONIZED);
        if ($registration->getMobile() !== null) {
            $registration->setMobile($this->stuHash->hash($registration->getMobile()));
        }
        $this->userRepository->save($user);

        $this->loggerUtil->log('Account wurde freigeschaltet');

        $context->setTemplateVar(
            'DISPLAY_FIRST_COLONY_DIALOGUE',
            $user->getState() === UserStateEnum::UNCOLONIZED
        );

        $this->lotteryFacade->createLotteryTicket($user, true);

        $this->sendWelcomeMessage->sendWelcomeMessage($user);

        $context->setView(GameController::DEFAULT_VIEW);

        $context->getInfo()->addInformation('Dein Account wurde erfolgreich freigeschaltet');
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
