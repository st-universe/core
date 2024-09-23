<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion;

use JBBCode\Parser;
use Noodlehaus\ConfigInterface;
use Override;
use Laminas\Mail\Exception\RuntimeException;
use Laminas\Mail\Message;
use Laminas\Mail\Transport\Sendmail;
use Stu\Component\Player\Deletion\Handler\PlayerDeletionHandlerInterface;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserRepositoryInterface;


final class PlayerDeletion implements PlayerDeletionInterface
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

    /**
     * @param array<PlayerDeletionHandlerInterface> $deletionHandler
     */
    public function __construct(
        private ConfigInterface $configs,
        private UserRepositoryInterface $userRepository,
        private StuConfigInterface $config,
        LoggerUtilFactoryInterface $loggerUtilFactory,
        private Parser $bbCodeParser,
        private array $deletionHandler
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    #[Override]
    public function handleDeleteable(): void
    {
        $this->loggerUtil->init('DEL', LoggerEnum::LEVEL_ERROR);

        //delete all accounts that have not been activated
        $list = $this->userRepository->getIdleRegistrations(
            time() - self::USER_IDLE_REGISTRATION
        );
        foreach ($list as $player) {
            $mail = new Message();
            $mail->addTo($player->getEmail());
            $mail->setSubject(_('Star Trek Universe - Löschung wegen Nichtaktivierung'));
            $mail->setFrom($this->configs->get('game.email_sender_address'));
            $mail->setBody(
                sprintf(
                    "Hallo %s.\n\n
            Du bekommst diese eMail, da Du deinen Account bisher nicht aktiviert hast.\n\n
            Daher wurde dein Account nun gelöscht.\n\n
            Wir würden uns freuen dich bei uns bald wieder zu sehen!\n\n
            Das Star Trek Universe Team\n
            %s",
                    $player->getName(),
                    $this->configs->get('game.base_url'),
                )
            );
            try {
                $transport = new Sendmail();
                $transport->send($mail);
            } catch (RuntimeException $e) {
                $this->loggerUtil->init("mail", LoggerEnum::LEVEL_ERROR);
                $this->loggerUtil->log($e->getMessage());
            }
            $this->delete($player);
        }

        //delete all other deleatable accounts
        $list = $this->userRepository->getDeleteable(
            time() - self::USER_IDLE_TIME,
            time() - self::USER_IDLE_TIME_VACATION,
            $this->config->getGameSettings()->getAdminIds()
        );
        foreach ($list as $player) {
            $mail = new Message();
            $mail->addTo($player->getEmail());
            $mail->setSubject(_('Star Trek Universe - Löschung wegen Inaktvität'));
            $mail->setFrom($this->configs->get('game.email_sender_address'));
            $mail->setBody(
                sprintf(
                    "Hallo %s.\n\n
            Du bekommst diese eMail, da Du seit längerem in Star Trek Universe inaktiv bist.\n\n
            Daher wurde dein Account nun gelöscht.\n\n
            Wir würden uns freuen dich bei uns bald wieder zu sehen!\n\n
            Das Star Trek Universe Team\n
            %s",
                    $player->getName(),
                    $this->configs->get('game.base_url'),
                )
            );
            try {
                $transport = new Sendmail();
                $transport->send($mail);
            } catch (RuntimeException $e) {
                $this->loggerUtil->init("mail", LoggerEnum::LEVEL_ERROR);
                $this->loggerUtil->log($e->getMessage());
            }
            $this->delete($player);
        }

        //warn all players that have been not activateted
        $list = $this->userRepository->getIdleRegistrations(
            time() - self::USER_IDLE_ONE_DAY
        );
        foreach ($list as $player) {
            $mail = new Message();
            $mail->addTo($player->getEmail());
            $mail->setSubject(_('Star Trek Universe - Löschung wegen Nichtaktivierung in 24h'));
            $mail->setFrom($this->configs->get('game.email_sender_address'));
            $mail->setBody(
                sprintf(
                    "Hallo %s.\n\n
    Du bekommst diese eMail, da Du dich in Star Trek Universe Registriert hast aber deinen Account noch nicht aktiviert hast. \n\n
    Sollte es Probleme bei der Registrierung gegeben haben (kein Passwort per Mail erhalten / keine Verifikations SMS erhalten), so kontaktiere uns bitte in unserem Forum, unserem Discord Chat oder per E-Mail.\n\n
    Wenn der Account nicht innerhalb von 24 Stunden aktiviert wird, wird dieser gelöscht.\n\n
    Das Star Trek Universe Team\n
    %s",
                    $player->getName(),
                    $this->configs->get('game.base_url'),
                )
            );
            try {
                $transport = new Sendmail();
                $transport->send($mail);
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
            $time = 0;
            if ($player->isVacationMode()) {
                if (time() - $player->getLastaction() > self::USER_IDLE_TIME_VACATION - self::USER_IDLE_ONE_DAY) {
                    $time = 24;
                } else if (time() - $player->getLastaction() > self::USER_IDLE_TIME_VACATION - self::USER_IDLE_TWO_DAYS) {
                    $time = 48;
                }
            } else {
                if (time() - $player->getLastaction() > self::USER_IDLE_TIME - self::USER_IDLE_ONE_DAY) {
                    $time = 24;
                } else if (time() - $player->getLastaction() > self::USER_IDLE_TIME - self::USER_IDLE_TWO_DAYS) {
                    $time = 48;
                }
            }


            $mail = new Message();
            $mail->addTo($player->getEmail());
            $mail->setSubject(sprintf('Star Trek Universe - Löschung wegen Inaktvität in %d Stunden', $time));
            $mail->setFrom($this->configs->get('game.email_sender_address'));
            $mail->setBody(
                sprintf(
                    "Hallo %s.\n\n
    Du bekommst diese eMail, da Du seit längerem in Star Trek Universe inaktiv bist.\n\n
    Wenn du dich nicht innerhalb von %d Stunden in deinen Account wieder einloggst, wird dieser gelöscht.\n\n
    Wir würden uns freuen dich bei uns wieder zu sehen!\n\n
    Das Star Trek Universe Team\n
    %s",
                    $player->getName(),
                    $time,
                    $this->configs->get('game.base_url'),
                )
            );
            try {
                $transport = new Sendmail();
                $transport->send($mail);
            } catch (RuntimeException $e) {
                $this->loggerUtil->init("mail", LoggerEnum::LEVEL_ERROR);
                $this->loggerUtil->log($e->getMessage());
            }
        }
    }

    #[Override]
    public function handleReset(): void
    {
        foreach ($this->userRepository->getNonNpcList() as $player) {
            $this->delete($player);
        }
    }

    private function delete(UserInterface $user): void
    {
        $userId = $user->getId();
        $name = $this->bbCodeParser->parse($user->getName())->getAsText();
        $delmark = $user->getDeletionMark();

        $this->loggerUtil->log(sprintf('deleting userId: %d', $userId));

        array_walk(
            $this->deletionHandler,
            function (PlayerDeletionHandlerInterface $handler) use ($user): void {
                $handler->delete($user);
            }
        );

        $this->loggerUtil->log(sprintf('deleted user (id: %d, name: %s, delmark: %d)', $userId, $name, $delmark));
    }
}
