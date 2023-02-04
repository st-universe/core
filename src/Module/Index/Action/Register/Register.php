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

        $factions = array_filter(
            $this->factionRepository->getByChooseable(true),
            function (FactionInterface $faction) use ($factionId): bool {
                return $factionId === $faction->getId() && $faction->hasFreePlayerSlots();
            }
        );
        if ($factions === []) {
            return;
        }

        $loginname = trim(mb_strtolower($this->registerRequest->getLoginName()));
        $email = trim(mb_strtolower($this->registerRequest->getEmailAddress()));

        $mobileNumber = $this->registerRequest->getMobileNumber();

        if ($mobileNumber === null) {
            return;
        }
        $this->playerCreator->createWithMobileNumber(
            $loginname,
            $email,
            current($factions),
            trim($mobileNumber)
        );

        $game->setView(ShowFinishRegistration::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
