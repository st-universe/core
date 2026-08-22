<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowSpacecraftDetails;

use request;
use Stu\Component\Crew\CrewTypeEnum;
use Stu\Component\Spacecraft\SpacecraftRumpRoleEnum;
use Stu\Component\Spacecraft\System\Type\UplinkShipSystem;
use Stu\Lib\Trait\SpacecraftTractorPayloadTrait;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Station\Lib\StationLoaderInterface;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Repository\UserCrewRankRepositoryInterface;

final class ShowSpacecraftDetails implements ViewControllerInterface
{
    use SpacecraftTractorPayloadTrait;

    public const string VIEW_IDENTIFIER = 'SHOW_SPACECRAFTDETAILS';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private StationLoaderInterface $stationLoader,
        private TroopTransferUtilityInterface $troopTransferUtility,
        private UserCrewRankRepositoryInterface $userCrewRankRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId,
            true,
            false
        );

        $game->setPageTitle('Schiffsinformationen');
        $game->setMacroInAjaxWindow('html/spacecraft/spacecraftDetails.twig');


        if ($wrapper->get()->isStation() && $game->getUser()->getAlliance() !== null) {
            $station = $this->stationLoader->getByIdAndUser(
                request::indInt('id'),
                $userId,
                true,
                false
            );

            $rumpRoleId = $station->getRump()->getShipRumpRole()?->getId();
            if (in_array($rumpRoleId, [SpacecraftRumpRoleEnum::DEPOT_SMALL, SpacecraftRumpRoleEnum::DEPOT_LARGE])) {
                $game->setTemplateVar('CAN_MANAGE_ALLIANCE', $station->getAlliance() === null);
            }
        }

        $game->setTemplateVar('WRAPPER', $wrapper);
        $game->setTemplateVar('USER_ID', $userId);

        $crewAssignments = $wrapper->get()->getCrewAssignments()->toArray();
        $positionOrder = [];
        foreach (CrewTypeEnum::getOrder() as $order => $position) {
            $positionOrder[$position->value] = $order;
        }

        $crewAssignmentComparator = static function (CrewAssignment $left, CrewAssignment $right) use ($positionOrder): int {
            $leftSlot = $left->getSlot();
            $rightSlot = $right->getSlot();
            $leftPositionOrder = $leftSlot === null ? count($positionOrder) : $positionOrder[$leftSlot->value];
            $rightPositionOrder = $rightSlot === null ? count($positionOrder) : $positionOrder[$rightSlot->value];

            if ($leftPositionOrder !== $rightPositionOrder) {
                return $leftPositionOrder <=> $rightPositionOrder;
            }

            $leftExpertise = $leftSlot === null ? 0 : $left->getCrew()->getSkillAt($leftSlot)?->getExpertise() ?? 0;
            $rightExpertise = $rightSlot === null ? 0 : $right->getCrew()->getSkillAt($rightSlot)?->getExpertise() ?? 0;

            return $rightExpertise <=> $leftExpertise
                ?: strcasecmp($left->getCrew()->getName(), $right->getCrew()->getName());
        };

        $ownCrewAssignments = [];
        $foreignCrewAssignments = [];
        $crewRankNames = [];
        foreach ($crewAssignments as $crewAssignment) {
            $crew = $crewAssignment->getCrew();
            $crewRankNames[$crew->getId()] = $this->userCrewRankRepository->getRankName($crew->getUser(), $crew->getRank());

            if ($crew->getUserId() === $userId) {
                $ownCrewAssignments[] = $crewAssignment;
            } else {
                $foreignCrewAssignments[] = $crewAssignment;
            }
        }
        usort($ownCrewAssignments, $crewAssignmentComparator);
        usort($foreignCrewAssignments, $crewAssignmentComparator);

        $game->setTemplateVar('OWN_CREW_ASSIGNMENTS', $ownCrewAssignments);
        $game->setTemplateVar('FOREIGN_CREW_ASSIGNMENTS', $foreignCrewAssignments);
        $game->setTemplateVar('CREW_RANK_NAMES', $crewRankNames);
        $game->setTemplateVar('TRACTOR_PAYLOAD', $this->getTractorPayload($wrapper->get()));

        $game->setTemplateVar('FOREIGNER_COUNT', $this->troopTransferUtility->foreignerCount($wrapper->get()));
        $game->setTemplateVar('MAX_FOREIGNERS', UplinkShipSystem::MAX_FOREIGNERS);
    }
}
