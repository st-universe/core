<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action;

use Laminas\Mail\Exception\RuntimeException;
use Laminas\Mail\Message;
use Laminas\Mail\Transport\Sendmail;
use Noodlehaus\ConfigInterface;
use request;
use Stu\Module\Admin\View\MassMail\MassMail;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Repository\UserRepositoryInterface;

final class SendMassMail implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_MASS_MAIL';

    private const MEDIUM_EMAIL = 1;
    private const MEDIUM_PM = 2;

    private ConfigInterface $config;

    private PrivateMessageSenderInterface $privateMessageSender;

    private UserRepositoryInterface $userRepository;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ConfigInterface $config,
        PrivateMessageSenderInterface $privateMessageSender,
        UserRepositoryInterface $userRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->config = $config;
        $this->privateMessageSender = $privateMessageSender;
        $this->userRepository = $userRepository;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(MassMail::VIEW_IDENTIFIER);

        $text = trim(request::postStringFatal('text'));
        $subject = trim(request::postStringFatal('subject'));
        $medium = request::postInt('medium');

        // only Admins can trigger ticks
        if (!$game->isAdmin()) {
            $game->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        $count = 0;
        if ($medium === static::MEDIUM_EMAIL) {
            $count = $this->sendMassEmails($text, $subject);
        } elseif ($medium === static::MEDIUM_PM) {
            $count = $this->sendMassPm($text, $subject);
        }

        $game->addInformationf(_('Die Massen-Mail wurde an %d Spieler verschickt.'), $count);
    }

    private function sendMassEmails(string $text, string $subject): int
    {
        $count = 0;

        foreach ($this->userRepository->findAll() as $user) {
            if ($user->isNpc()) {
                continue;
            }

            $mail = new Message();

            try {
                $mail->addTo($user->getEmail());
                $mail->setSubject($subject);
                $mail->setFrom($this->config->get('game.email_sender_address'));
                $mail->setBody($text);

                $transport = new Sendmail();
                $transport->send($mail);
                $count++;
            } catch (RuntimeException $e) {
                $this->loggerUtil->init("mail", LoggerEnum::LEVEL_ERROR);
                $this->loggerUtil->log(sprintf(
                    "Error while sending Mass-Mail to user-ID %d! Subject: %s",
                    $user->getId(),
                    $subject
                ));
            }
        }

        return $count;
    }

    private function sendMassPm(string $text, string $subject): int
    {
        $count = 0;

        $message = sprintf("[b]Betreff: %s[/b]\n\n%s", $subject, $text);

        foreach ($this->userRepository->findAll() as $user) {
            $count++;
            $this->privateMessageSender->send(
                UserEnum::USER_NOONE,
                $user->getId(),
                $message
            );
        }

        return $count;
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
