<?php

declare(strict_types=1);

namespace Stu\Module\Index\Action\Register;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\FactionInterface;
use Stu\Orm\Entity\ResearchInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\ResearchRepositoryInterface;
use User;
use UserData;

final class Register implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_SEND_REGISTRATION';

    private $registerRequest;

    private $researchRepository;

    private $researchedRepository;

    private $factionRepository;

    public function __construct(
        RegisterRequestInterface $registerRequest,
        ResearchRepositoryInterface $researchRepository,
        ResearchedRepositoryInterface $researchedRepository,
        FactionRepositoryInterface $factionRepository
    ) {
        $this->registerRequest = $registerRequest;
        $this->researchRepository = $researchRepository;
        $this->researchedRepository = $researchedRepository;
        $this->factionRepository = $factionRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $loginname = $this->registerRequest->getLoginName();
        $email = $this->registerRequest->getEmailAddress();
        $factionId = $this->registerRequest->getFactionId();
        if (!$game->isRegistrationPossible()) {
            return;
        }
        if (!preg_match('=^[a-zA-Z0-9]+$=i', $loginname)) {
            return;
        }
        if (mb_strlen($loginname) < 6) {
            return;
        }
        if (User::getByLogin($loginname)) {
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }
        if (User::getByEmail($email)) {
            return;
        }
        $factions = array_filter(
            $this->factionRepository->getByChooseable(true),
            function (FactionInterface $faction) use ($factionId): bool {
                return $factionId === $faction->getId() && $faction->hasFreePlayerSlots();
            }
        );
        if ($factions === []) {
            return;
        }
        $obj = new UserData([]);
        $obj->setLogin($loginname);
        $obj->setEmail($email);
        $obj->setFaction($factionId);
        $obj->save();
        $obj->setUser('Siedler ' . $obj->getId());
        $obj->setTick(1);
        // @todo
        // $obj->setTick(rand(1,8));
        $obj->setCreationDate(time());
        $obj->save();

        /**
         * @var ResearchInterface $research
         */
        $research = $this->researchRepository->find((int) $obj->getResearchStartId());

        $db = $this->researchedRepository->prototype();

        $db->setResearch($research);
        $db->setUserId($obj->getId());
        $db->setFinished(time());
        $db->setActive(0);

        $this->researchedRepository->save($db);

        $this->sendRegistrationEmail($obj);

        $game->setView('SHOW_REGISTRATION_END');
    }

    public function performSessionCheck(): bool
    {
        return false;
    }

    private function sendRegistrationEmail(UserData $obj)
    {
        $password = generatePassword();
        $obj->setPassword(sha1($password));
        $obj->save();

        $text = "Hallo " . $obj->getLogin() . "!\n\r\n\r";
        $text .= "Vielen Dank für Deine Anmeldung bei Star Trek Universe. Du kannst Dich nun mit folgendem Passwort und Deinem gewählten Loginnamen einloggen.\n\r\n\r";
        $text .= "Login: " . $obj->getLogin() . "\n\r";
        $text .= "Passwort: " . $password . "\n\r\n\r";
        $text .= "Bitte ändere das Passwort und auch Deinen Siedlernamen gleich nach Deinem Login.\n\r";
        $text .= "Und nun wünschen wir Dir viel Spaß!\n\r\n\r";
        $text .= "Das STU-Team\r\n\r\n";
        $text .= "https://stu.wolvnet.de";

        $header = "MIME-Version: 1.0\r\n";
        $header .= "Content-type: text/plain; charset=utf-8\r\n";
        $header .= "To: " . $obj->getEmail() . " <" . $obj->getEmail() . ">\r\n";
        $header .= "From: Star Trek Universe <automailer@stuniverse.de>\r\n";

        mail($obj->getEmail(), "Star Trek Universe Anmeldung", $text, $header);
    }
}
