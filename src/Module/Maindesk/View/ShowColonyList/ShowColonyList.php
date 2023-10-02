<?php

declare(strict_types=1);

namespace Stu\Module\Maindesk\View\ShowColonyList;

use Stu\Exception\AccessViolation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class ShowColonyList implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_COLONYLIST';

    private ColonyRepositoryInterface $colonyRepository;

    public function __construct(
        ColonyRepositoryInterface $colonyRepository
    ) {
        $this->colonyRepository = $colonyRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $state = $user->getState();

        if ($state !== UserEnum::USER_STATE_UNCOLONIZED) {
            throw new AccessViolation(sprintf(
                _('User is not uncolonized, but tried to enter first-colony-list. Fool: %d, State: %d'),
                $user->getId(),
                $state
            ));
        }
        $game->setTemplateFile("html/maindesk_colonylist.xhtml");
        $game->setPageTitle("Kolonie gründen");
        $game->appendNavigationPart(
            sprintf(
                '?%s=1',
                static::VIEW_IDENTIFIER
            ),
            _('Kolonie gründen')
        );

        $game->setTemplateVar(
            'FREE_PLANET_LIST',
            $this->colonyRepository->getStartingByFaction((int)$user->getFactionId())
        );
    }
}
