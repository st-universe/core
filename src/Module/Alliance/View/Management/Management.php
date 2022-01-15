<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Management;

use Stu\Component\Alliance\AllianceEnum;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class Management implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_MANAGEMENT';

    private ShipRumpRepositoryInterface $shipRumpRepository;

    private AllianceJobRepositoryInterface $allianceJobRepository;

    private ColonyRepositoryInterface $colonyRepository;

    private UserRepositoryInterface $userRepository;

    private AllianceActionManagerInterface $allianceActionManager;

    public function __construct(
        ShipRumpRepositoryInterface $shipRumpRepository,
        AllianceJobRepositoryInterface $allianceJobRepository,
        ColonyRepositoryInterface $colonyRepository,
        UserRepositoryInterface $userRepository,
        AllianceActionManagerInterface $allianceActionManager
    ) {
        $this->shipRumpRepository = $shipRumpRepository;
        $this->allianceJobRepository = $allianceJobRepository;
        $this->colonyRepository = $colonyRepository;
        $this->userRepository = $userRepository;
        $this->allianceActionManager = $allianceActionManager;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();
        $userId = $game->getUser()->getId();

        if ($alliance === null) {
            return;
        }

        $allianceId = $alliance->getId();

        if (!$this->allianceActionManager->mayEdit($allianceId, $userId)) {
            return;
        }

        $list = [];
        foreach ($this->userRepository->getByAlliance($allianceId) as $member) {
            $list[] = new ManagementListItemTal(
                $this->shipRumpRepository,
                $this->colonyRepository,
                $alliance,
                $member,
                $userId
            );
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
        $game->setTemplateVar('ALLIANCE_JOB_DIPLOMATIC', AllianceEnum::ALLIANCE_JOBS_DIPLOMATIC);
        $game->setTemplateVar('ALLIANCE_JOB_SUCCESSOR', AllianceEnum::ALLIANCE_JOBS_SUCCESSOR);
        $game->setTemplateVar('ALLIANCE_JOB_FOUNDER', AllianceEnum::ALLIANCE_JOBS_FOUNDER);
        $game->setTemplateVar('MEMBER_LIST', $list);
        $game->setTemplateVar(
            'USER_IS_FOUNDER',
            $this->allianceJobRepository->getSingleResultByAllianceAndType(
                $allianceId,
                AllianceEnum::ALLIANCE_JOBS_FOUNDER
            )->getUserId() === $userId
        );
    }
}
