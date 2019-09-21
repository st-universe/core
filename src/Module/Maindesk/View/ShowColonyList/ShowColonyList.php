<?php

declare(strict_types=1);

namespace Stu\Module\Maindesk\View\ShowColonyList;

use AccessViolation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class ShowColonyList implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_COLONYLIST';

    private $colonyRepository;

    public function __construct(
        ColonyRepositoryInterface $colonyRepository
    ) {
        $this->colonyRepository = $colonyRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        if ((int)$user->getActive() !== 1) {
            throw new AccessViolation();
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
            $this->colonyRepository->getStartingByFaction((int)$user->getFaction())
        );
    }
}
