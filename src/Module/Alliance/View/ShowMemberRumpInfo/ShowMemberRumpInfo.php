<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\ShowMemberRumpInfo;

use JBBCode\Parser;
use request;
use Stu\Component\Alliance\Enum\AllianceJobPermissionEnum;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;
use Stu\Orm\Repository\StationRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ShowMemberRumpInfo implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_MEMBER_RUMP_INFO';

    public function __construct(
        private AllianceJobManagerInterface $allianceJobManager,
        private UserRepositoryInterface $userRepository,
        private SpacecraftRumpRepositoryInterface $spacecraftRumpRepository,
        private Parser $bbcodeParser,
        private StationRepositoryInterface $stationRepository,
        private ShipRepositoryInterface $shipRepository,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $memberId = request::getIntFatal('userid');
        $rumpId = request::getIntFatal('rumpid');

        $member = $this->userRepository->find($memberId);
        if ($member === null) {
            return;
        }

        $memberAlliance = $member->getAlliance();
        if ($memberAlliance === null) {
            return;
        }

        if (
            !$this->allianceJobManager->hasUserPermission($user, $memberAlliance, AllianceJobPermissionEnum::SUCCESSOR)
            && !$this->allianceJobManager->hasUserPermission($user, $memberAlliance, AllianceJobPermissionEnum::DIPLOMATIC)
            && !$this->allianceJobManager->hasUserPermission($user, $memberAlliance, AllianceJobPermissionEnum::MANAGE_JOBS)
            && !$this->allianceJobManager->hasUserPermission($user, $memberAlliance, AllianceJobPermissionEnum::VIEW_COLONIES)
            && !$this->allianceJobManager->hasUserPermission($user, $memberAlliance, AllianceJobPermissionEnum::VIEW_MEMBER_DATA)
            && !$this->allianceJobManager->hasUserPermission($user, $memberAlliance, AllianceJobPermissionEnum::VIEW_SHIPS)
            && !$this->allianceJobManager->hasUserPermission($user, $memberAlliance, AllianceJobPermissionEnum::VIEW_ALLIANCE_STORAGE)
        ) {
            return;
        }

        $rump = $this->spacecraftRumpRepository->find($rumpId);

        if ($rump === null) {
            return;
        }

        $memberNameAsText = $this->bbcodeParser->parse($member->getName())->getAsText();
        $game->setPageTitle(sprintf(_('%s von Mitglied %s'), $rump->getName(), $memberNameAsText));
        $game->setMacroInAjaxWindow('html/alliance/alliancememberrumpinfo.twig');

        $spacecrafts = array_merge(
            $this->stationRepository->getByUserAndRump($member, $rump),
            $this->shipRepository->getByUserAndRump($member, $rump)
        );

        $game->setTemplateVar('WRAPPERS', $this->spacecraftWrapperFactory->wrapSpacecrafts($spacecrafts));
    }
}
