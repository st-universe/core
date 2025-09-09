<?php

declare(strict_types=1);

namespace Stu\Module\NPC\View\ShowPlayerList;

use Override;
use Stu\Component\Crew\CrewCountRetrieverInterface;
use Stu\Component\Player\CrewLimitCalculatorInterface;
use Stu\Module\Admin\Lib\UserlistEntry;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;

final class ShowPlayerList implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_PLAYER_LIST';

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private CrewCountRetrieverInterface $crewCountRetriever,
        private CrewLimitCalculatorInterface $crewLimitCalculator,
        private SpacecraftRumpRepositoryInterface $spacecraftRumpRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->appendNavigationPart(
            sprintf(
                '/npc/?%s=1',
                self::VIEW_IDENTIFIER
            ),
            _('Spielerliste')
        );
        $game->setTemplateFile('html/npc/playerList.twig');
        $game->setPageTitle(_('Spielerliste'));
        $game->setTemplateVar(
            'LIST',
            array_map(
                fn(User $user): UserlistEntry => new UserlistEntry(
                    $user,
                    $this->crewCountRetriever,
                    $this->crewLimitCalculator,
                    $this->spacecraftRumpRepository
                ),
                $this->userRepository->getNonNpcListbyFaction($game->getUser()->getFactionId())
            )
        );
    }
}