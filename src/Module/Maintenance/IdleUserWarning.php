<?php

namespace Stu\Module\Maintenance;

use Override;
use JBBCode\Parser;
use Noodlehaus\ConfigInterface;
use RuntimeException;
use Stu\Lib\Mail\MailFactoryInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class IdleUserWarning implements MaintenanceHandlerInterface
{
    // 1 day
    public const int USER_IDLE_ONE_DAY = 86400;

    // 2 days
    public const int USER_IDLE_TWO_DAYS = 172800;

    //3 days
    public const int USER_IDLE_REGISTRATION = 259200;

    //3 months
    public const int USER_IDLE_TIME = 7_905_600;

    //6 months
    public const int USER_IDLE_TIME_VACATION = 15_811_200;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private MailFactoryInterface $mailFactory,
        private ConfigInterface $configs,
        private StuConfigInterface $config,
        LoggerUtilFactoryInterface $loggerUtilFactory,
        private Parser $bbCodeParser
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    #[Override]
    public function handle(): void
    {
        $this->loggerUtil->init('DEL', LoggerEnum::LEVEL_ERROR);

        $notifiedEmails = [];

        //warn all accounts that have not been activated
        $list = $this->userRepository->getIdleRegistrations(
            time() - self::USER_IDLE_REGISTRATION
        );
        foreach ($list as $player) {
            $registration = $player->getRegistration();
            $playerName = $this->bbCodeParser->parse($player->getName())->getAsText();

            $mail = $this->mailFactory->createStuMail()
                ->withDefaultSender()
                ->addTo($registration->getEmail())
                ->setSubject(_('Star Trek Universe - Löschung wegen Nichtaktivierung'))
                ->setBody(
                    sprintf(
                        "Hallo %s.\n\n
            Du bekommst diese eMail, da Du deinen Account bisher nicht aktiviert hast.\n\n
            Daher wurde dein Account nun gelöscht.\n\n
            Wir würden uns freuen dich bei uns bald wieder zu sehen!\n\n
            Das Star Trek Universe Team\n
            %s",
                        $playerName,
                        $this->configs->get('game.base_url'),
                    )
                );
            try {
                $mail->send();
                $notifiedEmails[] = $registration->getEmail();
            } catch (RuntimeException $e) {
                $this->loggerUtil->init("mail", LoggerEnum::LEVEL_ERROR);
                $this->loggerUtil->log($e->getMessage());
            }
        }

        //warn all other deleatable accounts
        $list = $this->userRepository->getDeleteable(
            time() - self::USER_IDLE_TIME,
            time() - self::USER_IDLE_TIME_VACATION,
            $this->config->getGameSettings()->getAdminIds()
        );
        foreach ($list as $player) {
            $registration = $player->getRegistration();
            $playerName = $this->bbCodeParser->parse($player->getName())->getAsText();

            if ($registration->getDeletionMark() == UserEnum::DELETION_CONFIRMED) {
                $mail = $this->mailFactory->createStuMail()
                    ->withDefaultSender()
                    ->addTo($registration->getEmail())
                    ->setSubject(_('Star Trek Universe - Löschung des Accounts'))
                    ->setBody(
                        sprintf(
                            "Hallo %s.\n\n
            Du bekommst diese eMail, da dein Account in Star Trek Universe manuell zum löschen markiert wurde.\n\n
            Daher wurde dein Account nun gelöscht.\n\n
            Wir würden uns freuen dich bei uns bald wieder zu sehen!\n\n
            Das Star Trek Universe Team\n
            %s",
                            $playerName,
                            $this->configs->get('game.base_url'),
                        )
                    );
            } else {
                $mail = $this->mailFactory->createStuMail()
                    ->withDefaultSender()
                    ->addTo($registration->getEmail())
                    ->setSubject(_('Star Trek Universe - Löschung wegen Inaktvität'))
                    ->setBody(
                        sprintf(
                            "Hallo %s.\n\n
            Du bekommst diese eMail, da Du seit längerem in Star Trek Universe inaktiv bist.\n\n
            Daher wurde dein Account nun gelöscht.\n\n
            Wir würden uns freuen dich bei uns bald wieder zu sehen!\n\n
            Das Star Trek Universe Team\n
            %s",
                            $playerName,
                            $this->configs->get('game.base_url'),
                        )
                    );
            }
            try {
                $mail->send();
                $notifiedEmails[] = $registration->getEmail();
            } catch (RuntimeException $e) {
                $this->loggerUtil->init("mail", LoggerEnum::LEVEL_ERROR);
                $this->loggerUtil->log($e->getMessage());
            }
        }

        //warn all players that have been not activateted
        $list = $this->userRepository->getIdleRegistrations(
            time() - self::USER_IDLE_ONE_DAY
        );
        foreach ($list as $player) {

            $registration = $player->getRegistration();
            if (in_array($registration->getEmail(), $notifiedEmails)) {
                continue;
            }

            $playerName = $this->bbCodeParser->parse($player->getName())->getAsText();

            $mail = $this->mailFactory->createStuMail()
                ->withDefaultSender()
                ->addTo($registration->getEmail())
                ->setSubject(_('Star Trek Universe - Löschung wegen Nichtaktivierung in 24h'))
                ->setBody(
                    sprintf(
                        "Hallo %s.\n\n
        Du bekommst diese eMail, da Du dich in Star Trek Universe Registriert hast aber deinen Account noch nicht aktiviert hast. \n\n
    Sollte es Probleme bei der Registrierung gegeben haben (kein Passwort per Mail erhalten / keine Verifikations SMS erhalten), so kontaktiere uns bitte in unserem Forum, unserem Discord Chat oder per E-Mail.\n\n
    Wenn der Account nicht innerhalb von 24 Stunden aktiviert wird, wird dieser gelöscht.\n\n
    Das Star Trek Universe Team\n
    %s",
                        $playerName,
                        $this->configs->get('game.base_url'),
                    )
                );
            try {
                $mail->send();
                $notifiedEmails[] = $registration->getEmail();
            } catch (RuntimeException $e) {
                $this->loggerUtil->init("mail", LoggerEnum::LEVEL_ERROR);
                $this->loggerUtil->log($e->getMessage());
            }
        }

        //inform all 48/24h before deletion
        $list = $this->userRepository->getDeleteable(
            time() - self::USER_IDLE_TIME + self::USER_IDLE_TWO_DAYS,
            time() - self::USER_IDLE_TIME_VACATION + self::USER_IDLE_TWO_DAYS,
            $this->config->getGameSettings()->getAdminIds()
        );
        foreach ($list as $player) {
            $registration = $player->getRegistration();
            if (in_array($registration->getEmail(), $notifiedEmails)) {
                continue;
            }
            $playerName = $this->bbCodeParser->parse($player->getName())->getAsText();
            $time = 0;

            if ($player->isVacationMode()) {
                $idleTime = time() - $player->getLastaction();
                if ($idleTime > self::USER_IDLE_TIME_VACATION - self::USER_IDLE_TWO_DAYS && $idleTime <= self::USER_IDLE_TIME_VACATION - self::USER_IDLE_ONE_DAY) {
                    $time = 48;
                } elseif ($idleTime > self::USER_IDLE_TIME_VACATION - self::USER_IDLE_ONE_DAY && $idleTime <= self::USER_IDLE_TIME_VACATION) {
                    $time = 24;
                }
            } else {
                $idleTime = time() - $player->getLastaction();
                if ($idleTime > self::USER_IDLE_TIME - self::USER_IDLE_TWO_DAYS && $idleTime <= self::USER_IDLE_TIME - self::USER_IDLE_ONE_DAY) {
                    $time = 48;
                } elseif ($idleTime > self::USER_IDLE_TIME - self::USER_IDLE_ONE_DAY && $idleTime <= self::USER_IDLE_TIME) {
                    $time = 24;
                }
            }

            if ($time > 0) {
                $mail = $this->mailFactory->createStuMail()
                    ->withDefaultSender()
                    ->addTo($registration->getEmail())
                    ->setSubject(sprintf('Star Trek Universe - Löschung wegen Inaktvität in %d Stunden', $time))
                    ->setBody(
                        sprintf(
                            "Hallo %s.\n\n
    Du bekommst diese eMail, da Du seit längerem in Star Trek Universe inaktiv bist.\n\n
    Wenn du dich nicht innerhalb von %d Stunden in deinen Account wieder einloggst, wird dieser gelöscht.\n\n
    Wir würden uns freuen dich bei uns wieder zu sehen!\n\n
    Das Star Trek Universe Team\n
    %s",
                            $playerName,
                            $time,
                            $this->configs->get('game.base_url'),
                        )
                    );
                try {
                    $mail->send();
                } catch (RuntimeException $e) {
                    $this->loggerUtil->init("mail", LoggerEnum::LEVEL_ERROR);
                    $this->loggerUtil->log($e->getMessage());
                }
            }
        }
    }
}
