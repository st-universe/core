<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowScan;

use request;
use Stu\Component\Database\AchievementManagerInterface;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionTriggerEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Spacecraft\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\Station;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\SpacecraftLogRepositoryInterface;
use Stu\Orm\Repository\SpacecraftLogScanRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class ShowScan implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SCAN';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private InteractionCheckerInterface $interactionChecker,
        private PirateReactionInterface $pirateReaction,
        private PrivateMessageSenderInterface $privateMessageSender,
        private readonly AchievementManagerInterface $achievementManager,
        private readonly SpacecraftRepositoryInterface $spacecraftRepository,
        private readonly SpacecraftLogRepositoryInterface $spacecraftLogRepository,
        private readonly SpacecraftLogScanRepositoryInterface $spacecraftLogScanRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $shipId = request::indInt('id');
        $targetId = request::getIntFatal('target');

        $wrappers = $this->spacecraftLoader->getWrappersBySourceAndUserAndTarget(
            $shipId,
            $user->getId(),
            $targetId,
            true
        );

        $wrapper = $wrappers->getSource();
        $ship = $wrapper->get();

        $game->setMacroInAjaxWindow('html/entityNotAvailable.twig');

        $targetWrapper = $wrappers->getTarget();
        if ($targetWrapper === null) {
            return;
        }

        $target = $targetWrapper->get();
        if ($target->isCloaked()) {
            return;
        }

        $game->setPageTitle(_('Scan'));
        $game->setMacroInAjaxWindow('html/ship/showshipscan.twig');
        if (!$this->interactionChecker->checkPosition($ship, $target)) {
            $game->getInfo()->addInformation(_('Das Schiff befindet sich nicht in diesem Sektor'));
            return;
        }

        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem === null || $epsSystem->getEps() < 1) {
            $game->getInfo()->addInformation("Nicht genügend Energie vorhanden (1 benötigt)");
            return;
        }

        $epsSystem->lowerEps(1)->update();

        $this->achievementManager->checkDatabaseItem($target->getDatabaseId(), $user);
        $this->achievementManager->checkDatabaseItem($target->getRump()->getDatabaseId(), $user);

        $this->privateMessageSender->send(
            $game->getUser()->getId(),
            $target->getUser()->getId(),
            sprintf(
                _('Die %s von Spieler %s hat %s %s bei %s gescannt.'),
                $ship->getName(),
                $game->getUser()->getName(),
                $target->isStation() ? 'deine Station' : 'dein Schiff',
                $target->getName(),
                $target->getSectorString()
            ),
            $target->getType()->getMessageFolderType(),
            $target
        );

        $this->pirateReaction->checkForPirateReaction(
            $target,
            PirateReactionTriggerEnum::ON_SCAN,
            $ship
        );

        if ($ship->getCondition()->isDestroyed()) {
            return;
        }

        $scanDate = time();
        $scanLogbook = $this->spacecraftLogRepository->getBySpacecraftIdUntil($target->getId(), $scanDate, false);
        $this->saveSpacecraftLogScan($user, $target, $scanDate, $scanLogbook !== []);

        $game->setTemplateVar('TARGETWRAPPER', $targetWrapper);
        $game->setTemplateVar('SCAN_LOGBOOK', $scanLogbook);
        $game->setTemplateVar('SHIELD_PERCENTAGE', $this->calculateShieldPercentage($target));
        $game->setTemplateVar('REACTOR_PERCENTAGE', $this->calculateReactorPercentage($targetWrapper));
        $game->setTemplateVar('TRACTORING_SHIP', $this->getTractoringSpacecraft($target));
        $game->setTemplateVar('IS_TARGET_HELD_BY_THOLIAN_WEB', $target->getHoldingWeb() !== null);
        $game->setTemplateVar('CAN_SALVAGE_ESCAPE_PODS', $this->canSalvageEscapePods($target, $user->getId()));
        $game->setTemplateVar('SHIP', $ship);

        $tradePostCrewCount = null;
        $targetTradePost = $target instanceof Station ? $target->getTradePost() : null;

        if ($targetTradePost !== null) {
            $tradePostCrewCount = $targetTradePost->getCrewCountOfUser($user);
        }
        $game->setTemplateVar('TRADE_POST_CREW_COUNT', $tradePostCrewCount);
    }

    private function saveSpacecraftLogScan(User $user, Spacecraft $target, int $scanDate, bool $hasLogbook): void
    {
        if (!$hasLogbook) {
            return;
        }

        $this->spacecraftLogScanRepository->saveScan($user, $target->getId(), $scanDate);
    }

    private function calculateShieldPercentage(Spacecraft $target): int
    {
        return $target->getMaxShield() === 0
            ? 0
            : (int)ceil($target->getCondition()->getShield() / $target->getMaxShield() * 100);
    }

    private function getTractoringSpacecraft(Spacecraft $target): ?Spacecraft
    {
        return $target instanceof Ship
            ? $this->spacecraftRepository->getTractoringSpacecraft($target)
            : null;
    }

    private function canSalvageEscapePods(Spacecraft $target, int $userId): bool
    {
        $holdingWeb = $target->getHoldingWeb();

        return $holdingWeb === null || $holdingWeb->getUser()->getId() === $userId;
    }

    private function calculateReactorPercentage(SpacecraftWrapperInterface $wrapper): ?int
    {
        $reactor = $wrapper->getReactorWrapper();
        if ($reactor === null) {
            return null;
        }

        return $reactor->getCapacity() === 0
            ? 0
            : (int)ceil($reactor->getLoad() / $reactor->getCapacity() * 100);
    }
}
