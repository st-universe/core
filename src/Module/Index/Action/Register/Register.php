<?php

declare(strict_types=1);

namespace Stu\Module\Index\Action\Register;

use Exception;
use Stu\Module\Communication\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\FactionInterface;
use Stu\Orm\Entity\ResearchInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\ResearchRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class Register implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_SEND_REGISTRATION';

    private $registerRequest;

    private $researchRepository;

    private $researchedRepository;

    private $factionRepository;

    private $privateMessageFolderRepository;

    private $userRepository;

    public function __construct(
        RegisterRequestInterface $registerRequest,
        ResearchRepositoryInterface $researchRepository,
        ResearchedRepositoryInterface $researchedRepository,
        FactionRepositoryInterface $factionRepository,
        PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->registerRequest = $registerRequest;
        $this->researchRepository = $researchRepository;
        $this->researchedRepository = $researchedRepository;
        $this->factionRepository = $factionRepository;
        $this->privateMessageFolderRepository = $privateMessageFolderRepository;
        $this->userRepository = $userRepository;
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
        if ($this->userRepository->getByLogin($loginname)) {
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }
        if ($this->userRepository->getByEmail($email)) {
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
        $obj = $this->userRepository->prototype();
        $obj->setLogin($loginname);
        $obj->setEmail($email);
        $obj->setFaction(current($factions));

        $this->userRepository->save($obj);

        $obj->setUser('Siedler ' . $obj->getId());
        $obj->setTick(1);
        // @todo
        // $obj->setTick(rand(1,8));
        $obj->setCreationDate(time());
        $this->userRepository->save($obj);

        // Create default pm categories
        foreach (PrivateMessageFolderSpecialEnum::DEFAULT_CATEGORIES as $categoryId => $label) {
            $cat = $this->privateMessageFolderRepository->prototype();
            $cat->setUser($obj);
            $cat->setDescription(gettext($label));
            $cat->setSpecial($categoryId);
            $cat->setSort($categoryId);

            $this->privateMessageFolderRepository->save($cat);
        }

        /**
         * @var ResearchInterface $research
         */
        $research = $this->researchRepository->find($this->getResearchStartId($obj->getFaction()->getId()));

        $db = $this->researchedRepository->prototype();

        $db->setResearch($research);
        $db->setUser($obj);
        $db->setFinished(time());
        $db->setActive(0);

        $this->researchedRepository->save($db);

        $this->sendRegistrationEmail($obj);

        $game->setView('SHOW_REGISTRATION_END');
    }

    private function getResearchStartId(int $factionId): int
    {
        switch ($factionId) {
            case FACTION_FEDERATION:
                return RESEARCH_START_FEDERATION;
            case FACTION_ROMULAN:
                return RESEARCH_START_ROMULAN;
            case FACTION_KLINGON:
                return RESEARCH_START_KLINGON;
            case FACTION_CARDASSIAN:
                return RESEARCH_START_CARDASSIAN;
            case FACTION_FERENGI:
                return RESEARCH_START_FERENGI;
            case FACTION_EMPIRE:
                return RESEARCH_START_EMPIRE;
        }
        throw new Exception('Invalid faction');
    }


    public function performSessionCheck(): bool
    {
        return false;
    }

    private function sendRegistrationEmail(UserInterface $obj)
    {
        $password = generatePassword();
        $obj->setPassword(sha1($password));

        $this->userRepository->save($obj);

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
