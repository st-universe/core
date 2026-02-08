<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\SalvageEmergencyPods;

use request;
use Stu\Component\Ship\Retrofit\CancelRetrofitInterface;
use Stu\Component\Spacecraft\Repair\CancelRepairInterface;
use Stu\Lib\Interaction\InteractionCheckerBuilderFactoryInterface;
use Stu\Lib\Interaction\InteractionCheckType;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftRemoverInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class SalvageEmergencyPods implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SALVAGE_EPODS';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private CrewAssignmentRepositoryInterface $shipCrewRepository,
        private TradePostRepositoryInterface $tradePostRepository,
        private PrivateMessageSenderInterface $privateMessageSender,
        private TroopTransferUtilityInterface  $troopTransferUtility,
        private CancelRepairInterface $cancelRepair,
        private TransferToClosestLocation $transferToClosestLocation,
        private CancelRetrofitInterface $cancelRetrofit,
        private SpacecraftRemoverInterface $spacecraftRemover,
        private InteractionCheckerBuilderFactoryInterface $interactionCheckerBuilderFactory,
        private UserRepositoryInterface $userRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $wrappers = $this->spacecraftLoader->getWrappersBySourceAndUserAndTarget(
            request::postIntFatal('id'),
            $userId,
            request::postIntFatal('target'),
        );

        $wrapper = $wrappers->getSource();
        $spacecraft = $wrapper->get();
        $targetWrapper = $wrappers->getTarget();
        if ($targetWrapper === null) {
            return;
        }
        $target = $targetWrapper->get();

        if (!$this->interactionCheckerBuilderFactory
            ->createInteractionChecker()
            ->setSource($spacecraft)
            ->setTarget($target)
            ->setCheckTypes([
                InteractionCheckType::EXPECT_SOURCE_SUFFICIENT_CREW,
                InteractionCheckType::EXPECT_SOURCE_UNSHIELDED,
                InteractionCheckType::EXPECT_SOURCE_UNCLOAKED,
                InteractionCheckType::EXPECT_SOURCE_UNWARPED
            ])
            ->check($game->getInfo())) {
            return;
        }

        if ($target->getCrewCount() == 0) {
            $game->getInfo()->addInformation('Keine Rettungskapseln vorhanden');
            return;
        }
        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem === null || $epsSystem->getEps() < 1) {
            $game->getInfo()->addInformationf('Zum Bergen der Rettungskapseln wird %d Energie benötigt', 1);
            return;
        }
        if ($this->cancelRepair->cancelRepair($spacecraft)) {
            $game->getInfo()->addInformation("Die Reparatur wurde abgebrochen");
        }
        if ($spacecraft instanceof Ship && $this->cancelRetrofit->cancelRetrofit($spacecraft)) {
            $game->getInfo()->addInformation("Die Umrüstung wurde abgebrochen");
        }

        $crewmanPerUser = $this->determineCrewmanPerUser($target);
        if ($crewmanPerUser === []) {
            return;
        }

        //send PMs to crew owners
        $success = $this->sendPMsToCrewOwners($crewmanPerUser, $spacecraft, $target, $game);
        if (!$success) {
            return;
        }

        //remove entity if crew was on escape pods
        if ($target->getRump()->isEscapePods()) {
            $this->spacecraftRemover->remove($target);
        }

        $epsSystem->lowerEps(1)->update();
    }

    /**
     * @return array<int, int>
     */
    private function determineCrewmanPerUser(Spacecraft $target): array
    {
        $crewmanPerUser = [];

        foreach ($target->getCrewAssignments() as $shipCrew) {
            $crewUserId = $shipCrew->getCrew()->getUser()->getId();

            if (!array_key_exists($crewUserId, $crewmanPerUser)) {
                $crewmanPerUser[$crewUserId] = 1;
            } else {
                $crewmanPerUser[$crewUserId]++;
            }
        }

        return $crewmanPerUser;
    }

    /**
     * @param array<int, int> $crewmanPerUser
     */
    private function sendPMsToCrewOwners(
        array $crewmanPerUser,
        Spacecraft $spacecraft,
        Spacecraft $target,
        GameControllerInterface $game
    ): bool {
        $userId = $game->getUser()->getId();

        $sentGameInfoForForeignCrew = false;
        $closestTradepost = null;

        foreach ($crewmanPerUser as $ownerId => $count) {
            if ($ownerId !== $userId) {
                if ($closestTradepost === null) {
                    $crewOwner = $this->userRepository->find($ownerId);
                    if ($crewOwner === null) {
                        $game->getInfo()->addInformation('Crew-Besitzer nicht gefunden');
                        return false;
                    }

                    $closestTradepost = $this->tradePostRepository->getClosestTradePost($spacecraft->getLocation(), $crewOwner);
                    if ($closestTradepost === null) {
                        $game->getInfo()->addInformation('Kein Handelposten in der Nähe, an den die fremde Crew überstellt werden könnte');
                        return false;
                    }
                }

                $this->privateMessageSender->send(
                    UserConstants::USER_NOONE,
                    $ownerId,
                    sprintf(
                        _('Der Siedler %s hat %d deiner Crewmitglieder aus Rettungskapseln geborgen und an den Handelsposten "%s" (%s) überstellt.'),
                        $game->getUser()->getName(),
                        $count,
                        $closestTradepost->getName(),
                        $closestTradepost->getStation()->getSectorString()
                    ),
                    PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM
                );
                foreach ($target->getCrewAssignments() as $crewAssignment) {
                    if ($crewAssignment->getCrew()->getUser()->getId() === $ownerId) {
                        $crewAssignment->setSpacecraft(null);
                        $crewAssignment->setTradepost($closestTradepost);
                        $this->shipCrewRepository->save($crewAssignment);
                    }
                }
                if (!$sentGameInfoForForeignCrew) {
                    $game->getInfo()->addInformation(_('Die fremden Crewman wurde geborgen und an den dichtesten Handelsposten überstellt'));
                    $sentGameInfoForForeignCrew = true;
                }
            } elseif ($this->gotEnoughFreeTroopQuarters($spacecraft, $count)) {
                foreach ($target->getCrewAssignments() as $crewAssignment) {
                    if ($crewAssignment->getCrew()->getUser()->getId() === $game->getUser()->getId()) {
                        $crewAssignment->setSpacecraft($spacecraft);
                        $this->shipCrewRepository->save($crewAssignment);
                    }
                }
                $game->getInfo()->addInformationf(_('%d eigene Crewman wurde(n) auf dieses Schiff gerettet'), $count);
            } else {
                if ($closestTradepost === null) {
                    $closestTradepost = $this->tradePostRepository->getClosestTradePost($spacecraft->getLocation(), $game->getUser());
                }

                if ($closestTradepost === null) {
                    $game->getInfo()->addInformation('Kein Handelposten in der Nähe, an den die eigene Crew überstellt werden könnte');
                    return false;
                }

                $game->getInfo()->addInformation($this->transferToClosestLocation->transfer(
                    $spacecraft,
                    $target,
                    $count,
                    $closestTradepost
                ));
            }
        }

        return true;
    }

    private function gotEnoughFreeTroopQuarters(Spacecraft $spacecraft, int $count): bool
    {
        return $this->troopTransferUtility->getFreeQuarters($spacecraft) >= $count;
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
