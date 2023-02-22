<?php

declare(strict_types=1);

namespace Stu\Module\Maindesk\Action\SmsVerification;

use request;
use Stu\Lib\AccountNotVerifiedException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameController;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuHashInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Trade\Lib\LotteryFacadeInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class SmsVerification implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SMS_VERIFICATION';

    private UserRepositoryInterface $userRepository;

    private LotteryFacadeInterface $lotteryFacade;

    private StuHashInterface $stuHash;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        UserRepositoryInterface $userRepository,
        LotteryFacadeInterface $lotteryFacade,
        StuHashInterface $stuHash,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->userRepository = $userRepository;
        $this->lotteryFacade = $lotteryFacade;
        $this->stuHash = $stuHash;
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

        $user->setState(UserEnum::USER_STATE_UNCOLONIZED);
        $user->setMobile($this->stuHash->hash($user->getMobile()));
        $this->userRepository->save($user);

        $this->loggerUtil->log('Z');

        $game->setTemplateVar(
            'DISPLAY_FIRST_COLONY_DIALOGUE',
            $user->getState() === UserEnum::USER_STATE_UNCOLONIZED
        );

        $this->lotteryFacade->createLotteryTicket($user, true);

        $game->setView(GameController::DEFAULT_VIEW);

        $game->addInformation('Dein Account wurde erfolgreich freigeschaltet');
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
