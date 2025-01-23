<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\SalvageEmergencyPods;

use Override;
use request;
use Stu\Component\Spacecraft\Repair\CancelRepairInterface;
use Stu\Component\Ship\Retrofit\CancelRetrofitInterface;
use Stu\Lib\Interaction\InteractionCheckerBuilderFactoryInterface;
use Stu\Lib\Interaction\InteractionCheckType;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class SalvageEmergencyPods implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SALVAGE_EPODS';

    public function __construct(
        private ShipLoaderInterface $shipLoader,
        private CrewAssignmentRepositoryInterface $shipCrewRepository,
        private TradePostRepositoryInterface $tradePostRepository,
        private PrivateMessageSenderInterface $privateMessageSender,
        private TroopTransferUtilityInterface  $troopTransferUtility,
        private CancelRepairInterface $cancelRepair,
        private TransferToClosestLocation $transferToClosestLocation,
        private CancelRetrofitInterface $cancelRetrofit,
        private InteractionCheckerBuilderFactoryInterface $interactionCheckerBuilderFactory
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $wrappers = $this->shipLoader->getWrappersBySourceAndUserAndTarget(
            request::postIntFatal('id'),
            $userId,
            request::postIntFatal('target'),
        );

        $wrapper = $wrappers->getSource();
        $ship = $wrapper->get();
        $targetWrapper = $wrappers->getTarget();
        if ($targetWrapper === null) {
            return;
        }
        $target = $targetWrapper->get();

        if (!$this->interactionCheckerBuilderFactory
            ->createInteractionChecker()
            ->setSource($ship)
            ->setTarget($target)
            ->setCheckTypes([
                InteractionCheckType::EXPECT_SOURCE_SUFFICIENT_CREW,
                InteractionCheckType::EXPECT_SOURCE_UNSHIELDED,
                InteractionCheckType::EXPECT_SOURCE_UNCLOAKED,
                InteractionCheckType::EXPECT_SOURCE_UNWARPED
            ])
            ->check($game)) {
            return;
        }

        if (!$ship->hasEnoughCrew($game)) {
            return;
        }

        if ($target->getCrewCount() == 0) {
            $game->addInformation('Keine Rettungskapseln vorhanden');
            return;
        }
        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem === null || $epsSystem->getEps() < 1) {
            $game->addInformationf('Zum Bergen der Rettungskapseln wird %d Energie benötigt', 1);
            return;
        }
        if ($this->cancelRepair->cancelRepair($ship)) {
            $game->addInformation("Die Reparatur wurde abgebrochen");
        }
        if ($this->cancelRetrofit->cancelRetrofit($ship)) {
            $game->addInformation("Die Umrüstung wurde abgebrochen");
        }



        $crewmanPerUser = $this->determineCrewmanPerUser($target);
        if ($crewmanPerUser === []) {
            return;
        }

        $closestTradepost = $this->tradePostRepository->getClosestNpcTradePost($ship->getLocation());
        if ($closestTradepost === null) {
            $game->addInformation('Kein Handelposten in der Nähe, an den die Crew überstellt werden könnte');
            return;
        }

        //send PMs to crew owners
        $this->sendPMsToCrewOwners($crewmanPerUser, $ship, $target, $closestTradepost, $game);

        /**
         * TODO
         //remove entity if crew was on escape pods
         if ($target->getRump()->isEscapePods())
         {
             echo "- removeEscapePodEntity\n";
             $this->spacecraftRemover->remove($target);
            } else
            {
            }
         */

        $epsSystem->lowerEps(1)->update();

        $this->shipLoader->save($ship);
    }

    /**
     * @return array<int, int>
     */
    private function determineCrewmanPerUser(SpacecraftInterface $target): array
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
        SpacecraftInterface $spacecraft,
        SpacecraftInterface $target,
        TradePostInterface $closestTradepost,
        GameControllerInterface $game
    ): void {
        $userId = $game->getUser()->getId();

        $sentGameInfoForForeignCrew = false;

        foreach ($crewmanPerUser as $ownerId => $count) {
            if ($ownerId !== $userId) {
                $this->privateMessageSender->send(
                    UserEnum::USER_NOONE,
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
                    $game->addInformation(_('Die fremden Crewman wurde geborgen und an den dichtesten Handelsposten überstellt'));
                    $sentGameInfoForForeignCrew = true;
                }
            } elseif ($this->gotEnoughFreeTroopQuarters($spacecraft, $count)) {
                foreach ($target->getCrewAssignments() as $crewAssignment) {
                    if ($crewAssignment->getCrew()->getUser() === $game->getUser()) {
                        $crewAssignment->setSpacecraft($spacecraft);
                        $spacecraft->getCrewAssignments()->add($crewAssignment);
                        $this->shipCrewRepository->save($crewAssignment);
                    }
                }
                $game->addInformationf(_('%d eigene Crewman wurde(n) auf dieses Schiff gerettet'), $count);
            } else {
                $game->addInformation($this->transferToClosestLocation->transfer(
                    $spacecraft,
                    $target,
                    $count,
                    $closestTradepost
                ));
            }
        }
    }

    private function gotEnoughFreeTroopQuarters(SpacecraftInterface $spacecraft, int $count): bool
    {
        return $this->troopTransferUtility->getFreeQuarters($spacecraft) >= $count;
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
