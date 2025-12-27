<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\SalvageCrew;

use request;
use Stu\Component\Spacecraft\Crew\SpacecraftCrewCalculatorInterface;
use Stu\Component\Spacecraft\Repair\CancelRepairInterface;
use Stu\Component\Ship\Retrofit\CancelRetrofitInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Exception\SanityCheckException;
use Stu\Lib\Interaction\InteractionCheckerBuilderFactoryInterface;
use Stu\Lib\Interaction\InteractionCheckType;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Component\Spacecraft\System\Control\ActivatorDeactivatorHelperInterface;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\Station;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;

final class SalvageCrew implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SALVAGE_CREW';

    public function __construct(
        private ShipLoaderInterface $shipLoader,
        private CrewAssignmentRepositoryInterface $shipCrewRepository,
        private TroopTransferUtilityInterface  $troopTransferUtility,
        private ActivatorDeactivatorHelperInterface $helper,
        private CancelRepairInterface $cancelRepair,
        private SpacecraftCrewCalculatorInterface $shipCrewCalculator,
        private CancelRetrofitInterface $cancelRetrofit,
        private InteractionCheckerBuilderFactoryInterface $interactionCheckerBuilderFactory
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);
        $user = $game->getUser();
        $userId = $user->getId();

        $shipId = request::indInt('id');
        $targetId = request::postIntFatal('target');

        $wrappers = $this->shipLoader->getWrappersBySourceAndUserAndTarget(
            $shipId,
            $userId,
            $targetId
        );

        $wrapper = $wrappers->getSource();
        $ship = $wrapper->get();
        $targetWrapper = $wrappers->getTarget();
        if ($targetWrapper === null) {
            return;
        }
        $target = $targetWrapper->get();

        $tradepost = $target instanceof Station ? $target->getTradePost() : null;
        if ($tradepost === null) {
            throw new SanityCheckException('target is not a tradepost', self::ACTION_IDENTIFIER);
        }

        if (!$this->interactionCheckerBuilderFactory
            ->createInteractionChecker()
            ->setSource($ship)
            ->setTarget($target)
            ->setCheckTypes([
                InteractionCheckType::EXPECT_SOURCE_SUFFICIENT_CREW,
                InteractionCheckType::EXPECT_SOURCE_UNSHIELDED,
                InteractionCheckType::EXPECT_SOURCE_UNCLOAKED,
                InteractionCheckType::EXPECT_SOURCE_UNWARPED,
                InteractionCheckType::EXPECT_TARGET_UNWARPED,
                InteractionCheckType::EXPECT_TARGET_UNCLOAKED,
                InteractionCheckType::EXPECT_TARGET_UNSHIELDED
            ])
            ->check($game->getInfo())) {
            return;
        }

        if (!$ship->hasEnoughCrew($game)) {
            return;
        }

        if ($tradepost->getCrewCountOfUser($user) === 0) {
            throw new SanityCheckException('no crew to rescue', self::ACTION_IDENTIFIER);
        }
        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem === null || $epsSystem->getEps() < 1) {
            $game->getInfo()->addInformationf('Zum Bergen der Crew wird %d Energie benötigt', 1);
            return;
        }
        if ($this->cancelRepair->cancelRepair($ship)) {
            $game->getInfo()->addInformation("Die Reparatur wurde abgebrochen");
        }
        if ($this->cancelRetrofit->cancelRetrofit($ship)) {
            $game->getInfo()->addInformation("Die Umrüstung wurde abgebrochen");
        }

        $crewToTransfer = min(
            $this->troopTransferUtility->getFreeQuarters($ship),
            $tradepost->getCrewCountOfUser($user)
        );

        if (
            $ship->getCrewCount() + $crewToTransfer > $this->shipCrewCalculator->getMaxCrewCountByRump($ship->getRump())
            && $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::TROOP_QUARTERS)->getMode() == SpacecraftSystemModeEnum::MODE_OFF
            && !$this->helper->activate($wrapper, SpacecraftSystemTypeEnum::TROOP_QUARTERS, $game->getInfo())
        ) {
            return;
        }

        $game->getInfo()->addInformation(sprintf('Es wurden %d Crewman geborgen', $crewToTransfer));

        foreach ($tradepost->getCrewAssignments() as $crewAssignment) {
            if ($crewToTransfer === 0) {
                break;
            }
            if ($crewAssignment->getUser()->getId() !== $game->getUser()->getId()) {
                continue;
            }
            $crewAssignment->setTradepost(null);
            $crewAssignment->setSpacecraft($ship);
            $ship->getCrewAssignments()->add($crewAssignment);
            $this->shipCrewRepository->save($crewAssignment);

            $crewToTransfer--;
        }

        $epsSystem->lowerEps(1)->update();
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
