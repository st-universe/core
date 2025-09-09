<?php

declare(strict_types=1);

namespace Stu\Module\NPC\View\ShowPlayerDetails;

use Override;
use request;
use Stu\Component\Crew\CrewCountRetrieverInterface;
use Stu\Component\Player\CrewLimitCalculatorInterface;
use Stu\Module\Admin\Lib\UserlistEntry;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\NPC\Action\LogPlayerDetails;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;

final class ShowPlayerDetails implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_PLAYER_DETAILS';

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private CrewCountRetrieverInterface $crewCountRetriever,
        private CrewLimitCalculatorInterface $crewLimitCalculator,
        private SpacecraftRumpRepositoryInterface $spacecraftRumpRepository,
        private LogPlayerDetails $logPlayerDetailsAction
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateFile('html/npc/playerDetails.twig');

        $userId = request::getIntFatal('userid');
        $reason = request::getString('reason');

        if (!$game->isAdmin() && !$game->isNpc()) {
            return;
        }

        $user = $this->userRepository->find($userId);
        if ($user === null) {
            return;
        }

        if (!empty($reason)) {
            $logText = sprintf(
                '%s hat die Details von Spieler %s (%d) eingesehen. Grund: %s',
                $game->getUser()->getName(),
                $user->getName(),
                $user->getId(),
                $reason
            );

            if ($game->getUser()->isNpc()) {
                $this->logPlayerDetailsAction->createLogEntry($logText, $game->getUser()->getId());
            }
        }

        $game->setTemplateVar(
            'USER_DETAILS',
            new UserlistEntry(
                $user,
                $this->crewCountRetriever,
                $this->crewLimitCalculator,
                $this->spacecraftRumpRepository
            )
        );
    }
}