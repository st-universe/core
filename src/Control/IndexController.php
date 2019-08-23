<?php

namespace Stu\Control;

use Faction;
use InvalidParamException;
use request;
use Stu\Lib\LoginException;
use Stu\Lib\SessionInterface;
use Stu\Orm\Entity\ResearchInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\ResearchRepositoryInterface;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use SystemNews;
use User;
use UserData;
use Zend;
use Zend\Mail\Transport\Sendmail;

final class IndexController extends GameController
{

    private $default_tpl = "html/index.xhtml";

    private $session;

    private $researchedRepository;

    private $researchRepository;

    public function __construct(
        SessionInterface $session,
        ResearchedRepositoryInterface $researchedRepository,
        ResearchRepositoryInterface $researchRepository,
        SessionStringRepositoryInterface $sessionStringRepository
    ) {
        $this->session = $session;
        parent::__construct(
            $session,
            $sessionStringRepository,
            $this->default_tpl,
            "Star Trek Universe"
        );
        $this->addCallback('B_CHECK_REGVAR', 'checkRegistrationVar');
        $this->addCallback('B_SEND_REGISTRATION', 'registerUser');
        $this->addCallback('B_LOGIN', 'loginUser');
        $this->addCallBack('B_SEND_PASSWORD', 'sendPassword');
        $this->addCallBack('B_RESET_PASSWORD', 'resetPassword');

        $this->addView("SHOW_INFOS", "showInformations");
        $this->addView("SHOW_HELP", "showHelp");
        $this->addView("SHOW_REGISTRATION", "showRegistration");
        $this->addView("SHOW_REGISTRATION_END", "showRegistrationEnd");
        $this->addView('SHOW_LOST_PASSWORD', 'showLostPassword');
        $this->addView('SHOW_RESET_PASSWORD', 'showResetPassword');
        $this->researchedRepository = $researchedRepository;
        $this->researchRepository = $researchRepository;
    }

    protected function render(): void
    {
        $tpl = $this->getTemplate();
        $tpl->setVar('THIS', $this);
        $tpl->setVar("GFX", GFX_PATH);
        $tpl->parse();
    }

    public function main(bool $session_check = true): void
    {
        $this->session->checkLoginCookie();

        parent::main($session_check);
    }

    private $systemNews = null;

    function getSystemNews()
    {
        if ($this->systemNews === null) {
            $this->systemNews = SystemNews::getListBy("ORDER BY id ASC LIMIT 5");
        }
        return $this->systemNews;
    }

    protected function sendPassword()
    {
        // @todo inject by constructor
        global $config;

        $this->setView('SHOW_LOST_PASSWORD');
        $email_address = (string)request::indString('emailaddress');
        if (strlen($email_address) == 0) {
            $this->addInformation(_('Die eMail-Adresse ist nicht gültig'));
            return;
        }
        $user = User::getByEmail($email_address);
        if ($user === false) {
            $this->addInformation(_('Die eMail-Adresse ist nicht gültig'));
            return;
        }
        $token = $user->generatePasswordToken();
        $mail = new Zend\Mail\Message();
        $mail->addTo($user->getEmail());
        $mail->setSubject(_('Star Trek Universe - Password vergessen'));
        $mail->setFrom('automailer@stuniverse.de');
        $mail->setBody(
            sprintf("Hallo.\n\n
Du bekommst diese eMail, da Du in Star Trek Universe ein neues Password angefordert hast. Solltest Du das nicht getan
haben, so ignoriere die eMail einfach.\n\n
Klicke auf folgenden Link um Dir ein neues Password zu setzen:\n
%s/?SHOW_RESET_PASSWORD=1&TOKEN=%s\n\n
Das Strek Trek Universe Team\n
%s",
                $config->get('game.base_url'),
                $token,
                $config->get('game.base_url'),
            )
        );
        try {
            $transport = new Sendmail();
            $transport->send($mail);
        } catch (\Zend\Mail\Exception\RuntimeException $e) {
            $this->addInformation(_('Die eMail konnte nicht verschickt werden'));
            return;
        }
        $this->addInformation(_('Die eMail wurde verschickt'));
    }

    protected function resetPassword()
    {
        // @todo inject by constructor
        global $config;

        $token = (string)request::indString('TOKEN');
        $user = User::getByPasswordResetToken($token);
        if ($user === false) {
            throw new InvalidParamException;
        }
        $password = generatePassword();
        $user->setPassword(User::hashPassword($password));
        $user->setPasswordToken('');
        $user->save();
        $this->setView('SHOW_LOST_PASSWORD');
        $this->addInformation(_('Es wurde ein neues Passwort generiert und an die eMail-Adresse geschickt'));

        $mail = new Zend\Mail\Message();
        $mail->addTo($user->getEmail());
        $mail->setSubject(_('Star Trek Universe - Neues Passwort'));
        $mail->setFrom('automailer@stuniverse.de');
        $mail->setBody(
            sprintf("Hallo.\n\n
Du kannst Dich ab sofort mit folgendem Passwort in Star Trek Universe einloggen: %s\n\n
Das Star Trek Universe Team\n
%s",
                $password,
                $config->get('game.base_path'),
            )
        );
        try {
            $transport = new Sendmail();
            $transport->send($mail);
        } catch (\Zend\Mail\Exception\RuntimeException $e) {
            $this->addInformation(_('Die eMail konnte nicht verschickt werden'));
            return;
        }
    }

    protected function showResetPassword()
    {
        $token = (string)request::indString('TOKEN');
        $user = User::getByPasswordResetToken($token);
        if ($user === false) {
            throw new InvalidParamException;
        }
        $this->setTemplateFile('html/index_resetpassword.xhtml');
        $this->setPageTitle(_('Password zurücksetzen'));
        $this->getTemplate()->setVar('TOKEN', $user->getPasswordToken());
    }

    function showInformations()
    {
        $this->setTemplateFile('html/index_impressum.xhtml');
        $this->setPageTitle("Impressum");
    }

    function showHelp()
    {
        $this->setTemplateFile('html/index_help.xhtml');
        $this->setPageTitle("Hilfe");
    }

    function showRegistration()
    {
        $this->setTemplateFile('html/registration.xhtml');
        $this->setPageTitle("Registrierung");
    }

    function showRegistrationEnd()
    {
        $this->setTemplateFile('html/registration_end.xhtml');
        $this->setPageTitle("Registrierung");
    }

    protected function showLostPassword()
    {
        $this->setTemplateFile('html/index_lostpassword.xhtml');
        $this->setPageTitle(_('Password vergessen'));
    }

    private $gameStats = null;

    function getGameStats()
    {
        if ($this->gameStats === null) {
            $this->gameStats = $this->gatherGameStats();
        }
        return $this->gameStats;
    }

    function gatherGameStats()
    {
        $ret = array();
        $ret['turn'] = $this->getCurrentRound();
        $ret['player'] = $this->getPlayerCount();
        $ret['playeronline'] = $this->getOnlinePlayerCount();
        return $ret;
    }

    function getPossibleFactions()
    {
        return Faction::getChooseableFactions();
    }

    /**
     */
    public function isRegistrationPossible()
    {
        return true;
    }

    function checkRegistrationVar()
    {
        $var = request::getString('var');
        $value = request::getString('value');
        $state = REGISTER_STATE_NOK;
        switch ($var) {
            default:
            case 'loginname':
                if (!preg_match('=^[a-zA-Z0-9]+$=i', $value)) {
                    break;
                }
                if (strlen($value) < 6) {
                    break;
                }
                if (User::getByLogin($value)) {
                    $state = REGISTER_STATE_DUP;
                    break;
                }
                $state = REGISTER_STATE_OK;
                break;
            case 'email':
                $validator = new \Zend\Validator\EmailAddress();
                if (!$validator->isValid($value)) {
                    break;
                }
                if (User::getByEmail($value)) {
                    $state = REGISTER_STATE_DUP;
                    break;
                }
                $state = REGISTER_STATE_OK;
                break;
        }
        echo $state;
        exit;
    }

    function registerUser()
    {
        DB()->beginTransaction();
        $loginname = request::postString('loginname');
        $email = request::postString('email');
        $faction_id = request::postString('factionid');
        if (!$this->isRegistrationPossible()) {
            return;
        }
        if (!preg_match('=^[a-zA-Z0-9]+$=i', $loginname)) {
            return;
        }
        if (strlen($loginname) < 6) {
            return;
        }
        if (User::getByLogin($loginname)) {
            return;
        }
        $validator = new \Zend\Validator\EmailAddress();
        if (!$validator->isValid($email)) {
            return;
        }
        if (User::getByEmail($email)) {
            return;
        }
        $possible_factions = $this->getPossibleFactions();
        if (!array_key_exists($faction_id, $possible_factions)) {
            return;
        }
        $faction = $possible_factions[$faction_id];
        if (!$faction->hasFreePlayerSlots()) {
            return;
        }
        $obj = new UserData(array());
        $obj->setLogin($loginname);
        $obj->setEmail($email);
        $obj->setFaction($faction_id);
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

        DB()->commitTransaction();
        $this->sendRegistrationEmail($obj);

        $this->setView('SHOW_REGISTRATION_END');
    }

    function sendRegistrationEmail(UserData $obj)
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

    /**
     * @var ?string
     */
    private $login_error;

    /**
     */
    protected function loginUser()
    {
        try {
            $this->session->login(
                trim(request::postStringFatal('login')),
                trim(request::postStringFatal('pass'))
            );
        } catch (LoginException $e) {
            $this->login_error = $e->getMessage();
        }
    }

    public function hasLoginError(): bool
    {
        return $this->login_error !== null;
    }

    /**
     */
    public function getLoginError(): string
    {
        return (string) $this->login_error;
    }

    public function getGameStateTextual()
    {
        switch ($this->getGameState()) {
            case CONFIG_GAMESTATE_VALUE_ONLINE:
                return _('Online');
            case CONFIG_GAMESTATE_VALUE_MAINTENANCE:
                return _('Wartung');
            case CONFIG_GAMESTATE_VALUE_TICK:
                return _('Tick');
        }
    }
}
