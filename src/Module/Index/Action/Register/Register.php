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
        $loginname = $this->registerRequest->getLoginName();
        $email = $this->registerRequest->getEmailAddress();
        $factionId = $this->registerRequest->getFactionId();
        $token = $this->registerRequest->getToken();

        if (!$this->config->get('game.registration_enabled')) {
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

        try {
            $this->playerCreator->create(
                $loginname,
                $email,
                current($factions),
                $token
            );
        } catch (RegistrationException $e) {
            return;
        }

        $game->setView(ShowFinishRegistration::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
