<?php

declare(strict_types=1);

namespace Stu\Module\NPC\View\ShowPlayerDetails;

use request;
use Stu\Component\Crew\CrewCountRetrieverInterface;
use Stu\Component\Player\CrewLimitCalculatorInterface;
use Stu\Module\Admin\Lib\UserlistEntry;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ShowPlayerDetails implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_PLAYER_DETAILS';

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private CrewCountRetrieverInterface $crewCountRetriever,
        private CrewLimitCalculatorInterface $crewLimitCalculator,
        private SpacecraftRumpRepositoryInterface $spacecraftRumpRepository
    ) {}

    #[\Override]
    public function handle(ViewControllerContext $game): void
    {
        $game->setTemplateFile('html/npc/playerDetails.twig');

        $userId = request::indInt('userid');

        $user = $this->userRepository->find($userId);
        if ($user === null) {
            return;
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
