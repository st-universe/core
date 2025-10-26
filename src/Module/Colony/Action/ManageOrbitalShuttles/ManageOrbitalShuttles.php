<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ManageOrbitalShuttles;

use request;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\Lib\ShuttleManagementItem;
use Stu\Module\Colony\View\ShowOrbitManagement\ShowOrbitManagement;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Spacecraft\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Repository\CommodityRepositoryInterface;

final class ManageOrbitalShuttles implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_MANAGE_SHUTTLES';

    public function __construct(
        private ColonyLoaderInterface $colonyLoader,
        private PrivateMessageSenderInterface $privateMessageSender,
        private StorageManagerInterface $storageManager,
        private CommodityRepositoryInterface $commodityRepository,
        private ShipLoaderInterface $shipLoader,
        private InteractionCheckerInterface $interactionChecker
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowOrbitManagement::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $userId = $user->getId();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $userId
        );

        $wrapper = $this->shipLoader->find(request::indInt('sid'));
        if ($wrapper === null) {
            return;
        }

        $ship = $wrapper->get();

        if (!$this->interactionChecker->checkPosition($colony, $ship)) {
            return;
        }

        $isForeignShip = $userId !== $ship->getUser()->getId();

        $commodities = request::postArray('shuttles');
        if ($commodities === []) {
            return;
        }

        $shuttlecount = request::postArrayFatal('shuttlecount');

        if (array_sum($shuttlecount) > $ship->getRump()->getShuttleSlots()) {
            return;
        }

        $shuttles = [];

        foreach ($ship->getStorage() as $stor) {
            if ($stor->getCommodity()->isShuttle()) {
                $smi = new ShuttleManagementItem($stor->getCommodity());
                $smi->setCurrentLoad($stor->getAmount());

                $shuttles[$stor->getCommodity()->getId()] = $smi;
            }
        }

        foreach ($colony->getStorage() as $stor) {
            if ($stor->getCommodity()->isShuttle()) {
                if (array_key_exists($stor->getCommodity()->getId(), $shuttles)) {
                    $smi = $shuttles[$stor->getCommodity()->getId()];
                    $smi->setColonyLoad($stor->getAmount());
                } else {
                    $smi = new ShuttleManagementItem($stor->getCommodity());
                    $smi->setColonyLoad($stor->getAmount());

                    $shuttles[$stor->getCommodity()->getId()] = $smi;
                }
            }
        }

        $informations = new InformationWrapper();

        foreach ($commodities as $commodityId) {
            if (!is_numeric($commodityId)) {
                return;
            }

            $commodity = $this->commodityRepository->find((int)$commodityId);
            if ($commodity === null) {
                return;
            }

            $commodityId = $commodity->getId();

            if (!$commodity->isShuttle()) {
                return;
            }

            $wantedCount = (int)$shuttlecount[$commodityId];

            $smi = $shuttles[$commodityId];

            if ($wantedCount > $smi->getMaxUnits()) {
                continue;
            }

            if ($isForeignShip && $smi->getCurrentLoad() > $wantedCount) {
                continue;
            }

            if ($smi->getCurrentLoad() !== $wantedCount) {
                $this->transferShuttles(
                    $commodity,
                    $smi->getCurrentLoad(),
                    $wantedCount,
                    $ship,
                    $colony,
                    $informations
                );
            }
        }

        $game->getInfo()->addInformationWrapper($informations);

        if ($isForeignShip && $informations->getInformations() !== []) {
            $pm = sprintf(
                _("Die Kolonie %s des Spielers %s transferiert Shuttles in Sektor %d|%d\n%s"),
                $colony->getName(),
                $colony->getUser()->getName(),
                $ship->getPosX(),
                $ship->getPosY(),
                $informations->getInformationsAsString()
            );
            $this->privateMessageSender->send(
                $userId,
                $ship->getUser()->getId(),
                $pm,
                PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
                $ship
            );
        }
    }

    private function transferShuttles(
        Commodity $commodity,
        int $current,
        int $wanted,
        Ship $ship,
        Colony $colony,
        InformationWrapper $informations
    ): void {
        $diff = abs($wanted - $current);

        if ($current < $wanted) {
            $this->storageManager->upperStorage($ship, $commodity, $diff);
            $this->storageManager->lowerStorage($colony, $commodity, $diff);

            $msg = _('Es wurden %d %s zur %s transferiert');
        } else {
            $this->storageManager->lowerStorage($ship, $commodity, $diff);
            $this->storageManager->upperStorage($colony, $commodity, $diff);

            $msg = _('Es wurden %d %s von der %s transferiert');
        }

        $informations->addInformation(sprintf(
            $msg,
            $diff,
            $commodity->getName(),
            $ship->getName()
        ));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
