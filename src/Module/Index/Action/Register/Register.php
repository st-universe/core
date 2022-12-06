<?php

declare(strict_types=1);

namespace Stu\Module\Index\Action\Register;

use Noodlehaus\ConfigInterface;
use Stu\Component\Player\Register\Exception\RegistrationException;
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

    public function handle(GameControllerInterface $game): void
    {
        $factionId = $this->registerRequest->getFactionId();

        if (!$this->config->get('game.registration.enabled')) {
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

        $loginname = mb_strtolower($this->registerRequest->getLoginName());
        $email = $this->registerRequest->getEmailAddress();

        try {
            if ($this->config->get('game.registration.sms_code_verification.enabled')) {
                $mobileNumber = $this->registerRequest->getMobileNumber();

                if ($mobileNumber === null) {
                    return;
                }
                $this->registerViaSms($loginname, $email, $mobileNumber, $factions);
            } else {
                $this->registerViaToken($loginname, $email, $factions);
            }
        } catch (RegistrationException $e) {
            return;
        }

        $game->setView(ShowFinishRegistration::VIEW_IDENTIFIER);
    }

    private function registerViaSms(string $loginname, string $email, string $mobileNumber, array $factions): void
    {
        $this->playerCreator->createWithMobileNumber(
            $loginname,
            $email,
            current($factions),
            $mobileNumber
        );
    }

    private function registerViaToken(string $loginname, string $email, array $factions): void
    {
        $token = $this->registerRequest->getToken();

        $this->playerCreator->createViaToken(
            $loginname,
            $email,
            current($factions),
            $token
        );
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
