<?php

declare(strict_types=1);

namespace Stu\Module\Index\Action\Register;

use Noodlehaus\ConfigInterface;
use Stu\Component\Player\Register\PlayerCreatorInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Index\View\ShowFinishRegistration\ShowFinishRegistration;
use Stu\Orm\Entity\FactionInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;

final class Register implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SEND_REGISTRATION';

    private RegisterRequestInterface $registerRequest;

    private FactionRepositoryInterface $factionRepository;

    private PlayerCreatorInterface $playerCreator;

    private ConfigInterface $config;

    public function __construct(
        RegisterRequestInterface $registerRequest,
        FactionRepositoryInterface $factionRepository,
        PlayerCreatorInterface $playerCreator,
        ConfigInterface $config
    ) {
        $this->registerRequest = $registerRequest;
        $this->factionRepository = $factionRepository;
        $this->playerCreator = $playerCreator;
        $this->config = $config;
    }

    /**
     * @todo add registration without sms
     */
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
        $this->playerCreator->createWithMobileNumber(
            $loginname,
            $email,
            $faction['faction'],
            $mobileNumber
        );

        $game->setView(ShowFinishRegistration::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
