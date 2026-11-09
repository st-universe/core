<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Interaction\InteractionCheckerBuilderFactoryInterface;
use Stu\Lib\Interaction\InteractionCheckType;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\BorgSphereRaid;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\BorgSphereRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;

final class BorgSphereRaidService
{
    public function __construct(
        private BorgSphereRepositoryInterface $events,
        private BorgSphereRaidTeam $team,
        private InteractionCheckerBuilderFactoryInterface $interactionChecks,
        private CommodityRepositoryInterface $commodities,
        private StorageManagerInterface $storage,
        private PrivateMessageSenderInterface $messages,
        private StuRandom $random,
        private StuTime $time
    ) {}

    public function getCooldownUntil(User $user, BorgSphereEvent $event): int
    {
        $raid = $this->events->getRaid($user);
        return $raid === null || $raid->getLastAttemptAt() === 0 ? 0 : $raid->getLastAttemptAt() + $event->getConfiguration()->getSetting('raid_cooldown_seconds');
    }

    public function getTeamSize(Spacecraft $ship, BorgSphereEvent $event): int
    {
        return count($this->team->select($ship, $event->getConfiguration()->getSetting('raid_team_size')));
    }

    public function getCrewRequirementMessage(Spacecraft $ship): string
    {
        return sprintf('Für die Außenmission werden zusätzliche Crewmitglieder oberhalb der Mindestbesatzung des Bauplans (%d) benötigt. Besetzte Offiziersposten bleiben an Bord', $ship->getNeededCrewCount());
    }

    public function collect(SpacecraftWrapperInterface $wrapper, Spacecraft $target, InformationInterface $information): bool
    {
        $ship = $wrapper->get();
        $event = $this->availableEvent($target, $information);
        if ($event === null || !$this->checkInteraction($ship, $target, $information)) {
            return false;
        }
        $configuration = $event->getConfiguration();
        $raid = $this->availableRaid($ship->getUser(), $configuration, $information);
        if ($raid === null) {
            return false;
        }
        $team = $this->team->select($ship, $configuration->getSetting('raid_team_size'));
        $commodity = $this->availableCommodity($ship, $configuration, $team, $information);
        if ($commodity === null) {
            return false;
        }
        $raid->setAttempts($raid->getAttempts() + 1);
        [$success, $report] = $this->complete($wrapper, $event, $commodity, $team);
        if ($success) {
            $raid->setLastAttemptAt($this->time->time());
        }
        $this->events->saveRaid($raid);
        $information->addInformation($report);
        $this->messages->send(UserConstants::USER_NOONE, $ship->getUser()->getId(), $report, PrivateMessageFolderTypeEnum::SPECIAL_SHIP, $ship);
        return true;
    }

    private function availableEvent(Spacecraft $target, InformationInterface $information): ?BorgSphereEvent
    {
        $event = $this->events->getByShip($target);
        if ($event !== null) {
            $this->events->lockEvent($event);
        }
        if ($event === null || $event->isFinished() || !$event->getConfiguration()->isActive($this->time->time()) || $target->getCondition()->isDestroyed()) {
            $information->addInformation('Diese Borg-Sphäre ist nicht mehr für eine Außenmission verfügbar');
            return null;
        }
        return $event;
    }

    private function availableRaid(User $user, BorgSphereConfiguration $configuration, InformationInterface $information): ?BorgSphereRaid
    {
        $raid = $this->events->getRaid($user, true);
        if ($raid !== null && $raid->getLastAttemptAt() > 0 && $raid->getLastAttemptAt() + $configuration->getSetting('raid_cooldown_seconds') > $this->time->time()) {
            $information->addInformation(sprintf('Ein weiteres Außenteam kann ab %s Uhr entsendet werden', $this->time->transformToStuDateTime($raid->getLastAttemptAt() + $configuration->getSetting('raid_cooldown_seconds'))));
            return null;
        }
        return $raid ?? new BorgSphereRaid()->setUser($user);
    }

    /** @param list<CrewAssignment> $team */
    private function availableCommodity(Spacecraft $ship, BorgSphereConfiguration $configuration, array $team, InformationInterface $information): ?Commodity
    {
        if ($team === []) {
            $information->addInformation($this->getCrewRequirementMessage($ship));
            return null;
        }
        $commodity = $this->commodities->find($configuration->getSetting('nanite_commodity_id'));
        if ($commodity === null || $ship->getMaxStorage() - $ship->getStorageSum() < $configuration->getSetting('raid_reward_max')) {
            $information->addInformation(sprintf('Für die Außenmission werden %d freie Lagerplätze benötigt', $configuration->getSetting('raid_reward_max')));
            return null;
        }
        return $commodity;
    }

    /** @param list<CrewAssignment> $team
     * @return array{bool, string}
     */
    private function complete(SpacecraftWrapperInterface $wrapper, BorgSphereEvent $event, Commodity $commodity, array $team): array
    {
        $configuration = $event->getConfiguration();
        $ship = $wrapper->get();
        $chance = $configuration->getSetting($event->getPhase() === BorgSpherePhaseEnum::REGENERATING ? 'raid_success_disabled' : 'raid_success_active');
        $success = $this->random->rand(1, 100) <= $chance;
        if ($success) {
            $amount = $this->random->rand($configuration->getSetting('raid_reward_min'), $configuration->getSetting('raid_reward_max'));
            $this->storage->upperStorage($ship, $commodity, $amount);
            $report = sprintf('Das Außenteam ist von der Borg-Sphäre zurückgekehrt. %d %s wurden dem Lagerraum der %s hinzugefügt', $amount, $commodity->getName(), $ship->getName());
        } else {
            $lost = $this->team->assimilate($team, $this->random->rand($configuration->getSetting('raid_loss_min'), $configuration->getSetting('raid_loss_max')), $wrapper);
            $report = sprintf('Die Außenmission auf der Borg-Sphäre ist gescheitert. %d Crewmitglieder der %s wurden assimiliert und konnten nicht zurückgebeamt werden: %s', count($lost), $ship->getName(), implode(', ', $lost));
        }
        return [$success, $report];
    }

    private function checkInteraction(Spacecraft $ship, Spacecraft $target, InformationInterface $information): bool
    {
        if ($ship->getCondition()->isDisabled() || $ship->getWarpDriveState() || $target->getWarpDriveState()) {
            $information->addInformation('Die Außenmission erfordert ein einsatzfähiges Schiff außerhalb des Warps');
            return false;
        }
        return $this->interactionChecks->createInteractionChecker()->setSource($ship)->setTarget($target)->setCheckTypes([
            InteractionCheckType::EXPECT_SOURCE_ENABLED,
            InteractionCheckType::EXPECT_SOURCE_UNCLOAKED,
            InteractionCheckType::EXPECT_SOURCE_UNSHIELDED,
            InteractionCheckType::EXPECT_SOURCE_UNWARPED,
            InteractionCheckType::EXPECT_SOURCE_UNTRACTORED,
            InteractionCheckType::EXPECT_TARGET_UNCLOAKED,
            InteractionCheckType::EXPECT_TARGET_UNWARPED,
            InteractionCheckType::EXPECT_TARGET_ALSO_IN_FINISHED_WEB,
            InteractionCheckType::EXPECT_SPACECRAFT_BEAMABLE
        ])->check($information);
    }
}
