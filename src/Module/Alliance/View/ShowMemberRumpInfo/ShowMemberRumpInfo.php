<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\ShowMemberRumpInfo;

use request;
use JBBCode\Parser;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ShowMemberRumpInfo implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_MEMBER_RUMP_INFO';

    private AllianceActionManagerInterface $allianceActionManager;

    private UserRepositoryInterface $userRepository;

    private ShipRumpRepositoryInterface $shipRumpRepository;

    private Parser $bbcodeParser;

    private ShipRepositoryInterface $shipRepository;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    public function __construct(
        AllianceActionManagerInterface $allianceActionManager,
        UserRepositoryInterface $userRepository,
        ShipRumpRepositoryInterface $shipRumpRepository,
        Parser $bbcodeParser,
        ShipRepositoryInterface $shipRepository,
        ShipWrapperFactoryInterface $shipWrapperFactory
    ) {
        $this->allianceActionManager = $allianceActionManager;
        $this->userRepository = $userRepository;
        $this->shipRumpRepository = $shipRumpRepository;
        $this->bbcodeParser = $bbcodeParser;
        $this->shipRepository = $shipRepository;
        $this->shipWrapperFactory = $shipWrapperFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $game->setTemplateVar('ERROR', true);

        $memberId = request::getIntFatal('uid');
        $rumpId = request::getIntFatal('rid');

        $member = $this->userRepository->find($memberId);
        if ($member === null) {
            return;
        }

        $memberAlliance = $member->getAlliance();
        if ($memberAlliance === null) {
            return;
        }

        if (!$this->allianceActionManager->mayEdit($memberAlliance, $user)) {
            return;
        }

        $rump = $this->shipRumpRepository->find($rumpId);

        if ($rump === null) {
            return;
        }

        $memberNameAsText = $this->bbcodeParser->parse($member->getUserName())->getAsText();
        $game->setPageTitle(sprintf(_('%s von Mitglied %s'), $rump->getName(), $memberNameAsText));
        $game->setMacroInAjaxWindow('html/alliancemacros.xhtml/memberrumpinfo');

        $ships = $this->shipRepository->getByUserAndRump($memberId, $rumpId);

        $game->setTemplateVar('WRAPPERS', $this->shipWrapperFactory->wrapShips($ships));
        $game->setTemplateVar('ERROR', false);
    }
}
