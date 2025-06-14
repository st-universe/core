<?php

declare(strict_types=1);

namespace Stu\Module\Index\Action\Register;

use Noodlehaus\ConfigInterface;
use Override;
use Stu\Component\Player\Register\PlayerCreatorInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Index\View\ShowFinishRegistration\ShowFinishRegistration;
use Stu\Orm\Entity\FactionInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;

final class Register implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SEND_REGISTRATION';

    public function __construct(private RegisterRequestInterface $registerRequest, private FactionRepositoryInterface $factionRepository, private PlayerCreatorInterface $playerCreator, private ConfigInterface $config) {}

    /**
     * @todo add registration without sms
     */
    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        if (!$this->config->get('game.registration.enabled')) {
            return;
        }

        $factionId = $this->registerRequest->getFactionId();

        /** @var null|array{faction: FactionInterface, count: int} $faction */
        $faction = $this->factionRepository->getPlayableFactionsPlayerCount()[$factionId] ?? null;

        if ($faction === null) {
            return;
        }

        $playerLimit = $faction['faction']->getPlayerLimit();

        if (
            $playerLimit !== 0
            && $playerLimit <= $faction['count']
        ) {
            return;
        }

        $loginname = trim(mb_strtolower($this->registerRequest->getLoginName()));
        $email = trim(mb_strtolower($this->registerRequest->getEmailAddress()));
        $referer = $this->registerRequest->getReferer();

        $countryCode = $this->registerRequest->getCountryCode();
        $mobile = $this->registerRequest->getMobileNumber();

        if (preg_match('/^(\\+49|\\+43|\\+41|0+)/', $mobile, $matches)) {
            $mobile = substr($mobile, strlen($matches[0]));
        }
        $mobile = str_replace(' ', '', $mobile);


        $mobileNumber  = $countryCode . $mobile;

        if ($mobileNumber === '') {
            return;
        }
        $password = trim($this->registerRequest->getPassword());
        $passwordReEntered = $this->registerRequest->getPasswordReEntered();

        if ($password === '') {
            return;
        }
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*[^A-Za-z0-9]).{6,}$/', $password)) {
            return;
        }
        if ($password !== $passwordReEntered) {
            return;
        }

        $this->playerCreator->createWithMobileNumber(
            $loginname,
            $email,
            $faction['faction'],
            $mobileNumber,
            $password,
            $referer
        );


        $game->setView(ShowFinishRegistration::VIEW_IDENTIFIER);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
