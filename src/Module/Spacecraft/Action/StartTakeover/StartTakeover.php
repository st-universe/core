<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\StartTakeover;

use Override;
use request;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Exception\SanityCheckException;
use Stu\Lib\Interaction\InteractionCheckerBuilderFactoryInterface;
use Stu\Lib\Interaction\InteractionCheckType;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Maindesk\Action\ColonizationShip\ColonizationShip;
use Stu\Module\Spacecraft\Lib\Battle\FightLibInterface;
use Stu\Module\Spacecraft\Lib\Interaction\ShipTakeoverManagerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftStateChangerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

final class StartTakeover implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_TAKEOVER_SHIP';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private FightLibInterface $fightLib,
        private ShipTakeoverManagerInterface $shipTakeoverManager,
        private SpacecraftStateChangerInterface $spacecraftStateChanger,
        private InteractionCheckerBuilderFactoryInterface $interactionCheckerBuilderFactory
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $userId = $user->getId();

        $shipId = request::getIntFatal('id');
        $targetId = request::getIntFatal('target');

        $wrappers = $this->spacecraftLoader->getWrappersBySourceAndUserAndTarget(
            $shipId,
            $userId,
            $targetId
        );

        $wrapper = $wrappers->getSource();
        $spacecraft = $wrapper->get();

        if ($spacecraft->getTakeoverActive() !== null) {
            return;
        }

        $targetWrapper = $wrappers->getTarget();
        if ($targetWrapper === null) {
            return;
        }
        $target = $targetWrapper->get();


        if (!$target->isBoardingPossible()) {
            return;
        }

        if ($target->getUser()->getId() === $user->getId()) {
            return;
        }

        if (!$this->interactionCheckerBuilderFactory
            ->createInteractionChecker()
            ->setSource($spacecraft)
            ->setTarget($target)
            ->setCheckTypes([
                InteractionCheckType::EXPECT_SOURCE_ENABLED,
                InteractionCheckType::EXPECT_SOURCE_SUFFICIENT_CREW,
                InteractionCheckType::EXPECT_SOURCE_TACHYON,
                InteractionCheckType::EXPECT_TARGET_NO_VACATION,
                InteractionCheckType::EXPECT_TARGET_ALSO_IN_FINISHED_WEB
            ])
            ->check($game->getInfo())) {
            return;
        }

        if (!$this->fightLib->canAttackTarget($spacecraft, $target, false, false)) {
            throw new SanityCheckException('Target cant be attacked', self::ACTION_IDENTIFIER);
        }

        $epsSystemData = $wrapper->getEpsSystemData();
        if ($epsSystemData === null || $epsSystemData->getEps() === 0) {
            $game->getInfo()->addInformation(_('Keine Energie vorhanden'));
            return;
        }

        if (!$target->getCrewAssignments()->isEmpty()) {
            $game->getInfo()->addInformation(_('Aktion nicht möglich, das Ziel ist bemannt'));
            return;
        }

        if ($target->getRumpId() && in_array($target->getRumpId(), [
            ColonizationShip::FED_COL_BUILDPLAN,
            ColonizationShip::ROM_COL_BUILDPLAN,
            ColonizationShip::KLING_COL_BUILDPLAN,
            ColonizationShip::CARD_COL_BUILDPLAN,
            ColonizationShip::FERG_COL_BUILDPLAN
        ])) {
            $game->getInfo()->addInformation(_('Dieses Schiff ist nicht zur Übernahme geeignet'));
            return;
        }


        if ($target->getTakeoverPassive() !== null) {
            $game->getInfo()->addInformationf(
                'Aktion nicht möglich, das Ziel ist bereits im Begriff von der %s übernommen zu werden',
                $target->getTakeoverPassive()->getSourceSpacecraft()->getName()
            );
            return;
        }

        $neededPrestige = $this->shipTakeoverManager->getPrestigeForTakeover($target);
        if ($user->getPrestige() < $neededPrestige) {
            $game->getInfo()->addInformation(sprintf(
                'Nicht genügend Prestige vorhanden, benötigt wird: %d',
                $neededPrestige
            ));
            return;
        }

        $this->spacecraftStateChanger->changeState($wrapper, SpacecraftStateEnum::ACTIVE_TAKEOVER);
        $this->shipTakeoverManager->startTakeover($spacecraft, $target, $neededPrestige);

        $game->getInfo()->addInformationf(
            'Übernahme der %s wurde gestartet. Fertigstellung in %d Runden.',
            $target->getName(),
            ShipTakeoverManagerInterface::TURNS_TO_TAKEOVER
        );
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
