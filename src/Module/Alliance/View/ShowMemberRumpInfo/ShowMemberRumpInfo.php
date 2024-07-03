<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\ShowMemberRumpInfo;

use Override;
use JBBCode\Parser;
use request;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ShowMemberRumpInfo implements ViewControllerInterface
{
    /**
     * @var string
     */
    public const string VIEW_IDENTIFIER = 'SHOW_MEMBER_RUMP_INFO';

    public function __construct(private AllianceActionManagerInterface $allianceActionManager, private UserRepositoryInterface $userRepository, private ShipRumpRepositoryInterface $shipRumpRepository, private Parser $bbcodeParser, private ShipRepositoryInterface $shipRepository, private ShipWrapperFactoryInterface $shipWrapperFactory)
    {
    }

    #[Override]
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

        $memberNameAsText = $this->bbcodeParser->parse($member->getName())->getAsText();
        $game->setPageTitle(sprintf(_('%s von Mitglied %s'), $rump->getName(), $memberNameAsText));
        $game->setMacroInAjaxWindow('html/alliancemacros.xhtml/memberrumpinfo');

        $ships = $this->shipRepository->getByUserAndRump($memberId, $rumpId);

        $game->setTemplateVar('WRAPPERS', $this->shipWrapperFactory->wrapShips($ships));
        $game->setTemplateVar('ERROR', false);
    }
}
