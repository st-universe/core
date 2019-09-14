<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Management;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;
use User;

final class Management implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_MANAGEMENT';

    private $shipRumpRepository;

    private $allianceJobRepository;

    public function __construct(
        ShipRumpRepositoryInterface $shipRumpRepository,
        AllianceJobRepositoryInterface $allianceJobRepository
    ) {
        $this->shipRumpRepository = $shipRumpRepository;
        $this->allianceJobRepository = $allianceJobRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();
        $userId = $game->getUser()->getId();
        $allianceId = (int) $alliance->getId();

        $list = [];
        foreach (User::getListBy('WHERE allys_id=' . $allianceId) as $member) {
            $list[] = new ManagementListItemTal($this->shipRumpRepository, $alliance, $member, $userId);
        }

        $game->setPageTitle(_('Allianz verwalten'));
        $game->appendNavigationPart(
            'alliance.php',
            _('Allianz')
        );
        $game->appendNavigationPart(
            'alliance.php?SHOW_MANAGEMENT=1',
            _('Verwaltung')
        );
        $game->setTemplateFile('html/alliancemanagement.xhtml');
        $game->setTemplateVar('ALLIANCE', $alliance);
        $game->setTemplateVar('ALLIANCE_JOB_DIPLOMATIC', ALLIANCE_JOBS_DIPLOMATIC);
        $game->setTemplateVar('ALLIANCE_JOB_SUCCESSOR', ALLIANCE_JOBS_SUCCESSOR);
        $game->setTemplateVar('MEMBER_LIST', $list);
        $game->setTemplateVar(
            'USER_IS_FOUNDER',
            $this->allianceJobRepository->getSingleResultByAllianceAndType(
                $allianceId,
                ALLIANCE_JOBS_FOUNDER
            )->getUserId() === $userId
        );
    }
}
