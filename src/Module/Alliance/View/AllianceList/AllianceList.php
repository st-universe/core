<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\AllianceList;

use Stu\Module\Alliance\Lib\AllianceListItem;
use Stu\Module\Alliance\Lib\AllianceListItemInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;

final class AllianceList implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_LIST';

    private AllianceRepositoryInterface $allianceRepository;

    public function __construct(
        AllianceRepositoryInterface $allianceRepository
    ) {
        $this->allianceRepository = $allianceRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setPageTitle(_('Allianzliste'));

        if ($game->getUser()->getAllianceId() > 0) {
            $game->appendNavigationPart(
                'alliance.php',
                _('Allianz')
            );
        }

        $game->appendNavigationPart('alliance.php?SHOW_LIST=1', _('Allianzliste'));
        $game->setTemplateFile('html/alliancelist.xhtml');

        $game->setTemplateVar(
            'ALLIANCE_LIST_OPEN',
            array_map(
                function (AllianceInterface $alliance): AllianceListItemInterface {
                    return new AllianceListItem($alliance);
                },
                $this->allianceRepository->findByApplicationState(true)
            )
        );
        $game->setTemplateVar(
            'ALLIANCE_LIST_CLOSED',
            array_map(
                function (AllianceInterface $alliance): AllianceListItemInterface {
                    return new AllianceListItem($alliance);
                },
                $this->allianceRepository->findByApplicationState(false)
            )
        );
    }
}
