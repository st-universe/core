<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\ShowMemberRumpInfo;

use request;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ShowMemberRumpInfo implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_MEMBER_RUMP_INFO';

    private AllianceActionManagerInterface $allianceActionManager;

    private UserRepositoryInterface $userRepository;

    private ShipRumpRepositoryInterface $shipRumpRepository;

    private ShipRepositoryInterface $shipRepository;

    public function __construct(
        AllianceActionManagerInterface $allianceActionManager,
        UserRepositoryInterface $userRepository,
        ShipRumpRepositoryInterface $shipRumpRepository,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->allianceActionManager = $allianceActionManager;
        $this->userRepository = $userRepository;
        $this->shipRumpRepository = $shipRumpRepository;
        $this->shipRepository = $shipRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $game->setTemplateVar('ERROR', true);

        $memberId = request::getIntFatal('uid');
        $rumpId = request::getIntFatal('rid');

        $member = $this->userRepository->find($memberId);

        if ($member->getAlliance() === null) {
            return;
        }

        $allianceId = $member->getAlliance()->getId();

        if (!$this->allianceActionManager->mayEdit($allianceId, $userId)) {
            return;
        }

        $rump = $this->shipRumpRepository->find($rumpId);

        if ($rump === null) {
            return;
        }

        $game->setPageTitle(sprintf(_('%s von Mitglied %s'), $rump->getName(), $member->getUserName()));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/alliancemacros.xhtml/memberrumpinfo');

        $ships = $this->shipRepository->getByUserAndRump($memberId, $rumpId);

        $game->setTemplateVar('SHIPS', $ships);
        $game->setTemplateVar('ERROR', false);
    }
}
