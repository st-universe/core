<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\ManageShuttles;

use Override;
use request;
use RuntimeException;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Colony\Lib\ShuttleManagementItem;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Spacecraft\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Station\Lib\StationLoaderInterface;
use Stu\Module\Station\View\ShowShipManagement\ShowShipManagement;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\StationInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;

final class ManageShuttles implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_MANAGE_SHUTTLES';

    public function __construct(
        private StationLoaderInterface $stationLoader,
        private PrivateMessageSenderInterface $privateMessageSender,
        private StorageManagerInterface $storageManager,
        private CommodityRepositoryInterface $commodityRepository,
        private InteractionCheckerInterface $interactionChecker
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShipManagement::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $userId = $user->getId();

        $stationId = request::indInt('id');
        $shipId = request::indInt('sid');

        $wrappers = $this->stationLoader->getWrappersBySourceAndUserAndTarget(
            $stationId,
            $userId,
            $shipId
        );

        $wrapper = $wrappers->getSource();
        $station = $wrapper->get();

        $targetWrapper = $wrappers->getTarget();
        if ($targetWrapper === null) {
            return;
        }
        $ship = $targetWrapper->get();

        if (!$this->interactionChecker->checkPosition($station, $ship)) {
            return;
        }

        $isForeignShip = $userId !== $ship->getUser()->getId();

        $commodities = request::postArray('shuttles');
        $shuttlecount = request::postArrayFatal('shuttlecount');

        if (array_sum($shuttlecount) > $ship->getRump()->getShuttleSlots()) {
            return;
        }

        $shuttles = [];
        $currentlyStored = 0;

        foreach ($ship->getStorage() as $stor) {
            if ($stor->getCommodity()->isShuttle()) {
                $smi = new ShuttleManagementItem($stor->getCommodity());
                $smi->setCurrentLoad($stor->getAmount());
                $currentlyStored += $stor->getAmount();

                $shuttles[$stor->getCommodity()->getId()] = $smi;
            }
        }

        foreach ($station->getStorage() as $stor) {
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
            $wantedCount = (int)$shuttlecount[$commodityId];

            $smi = $shuttles[(int)$commodityId];

            if ($wantedCount > $smi->getMaxUnits()) {
                continue;
            }

            if ($isForeignShip && $smi->getCurrentLoad() > $wantedCount) {
                continue;
            }

            if ($smi->getCurrentLoad() !== $wantedCount) {
                $informations->addInformation($this->transferShuttles(
                    (int)$commodityId,
                    $smi->getCurrentLoad(),
                    $wantedCount,
                    $ship,
                    $station
                ));
            }
        }

        $game->addInformationWrapper($informations);

        if ($isForeignShip && !$informations->isEmpty()) {
            $pm = sprintf(
                _("Die %s %s des Spielers %s transferiert Shuttles in Sektor %d|%d\n%s"),
                $station->getRump()->getName(),
                $station->getName(),
                $station->getUser()->getName(),
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
        int $commodityId,
        int $current,
        int $wanted,
        SpacecraftInterface $spacecraft,
        StationInterface $station
    ): string {
        $commodity = $this->commodityRepository->find($commodityId);
        if ($commodity === null) {
            throw new RuntimeException(sprintf('commodityId %d does not exist', $commodityId));
        }
        $diff = abs($wanted - $current);

        if ($current < $wanted) {
            $this->storageManager->upperStorage($spacecraft, $commodity, $diff);
            $this->storageManager->lowerStorage($station, $commodity, $diff);

            $msg = _('Es wurden %d %s zur %s transferiert');
        } else {
            $this->storageManager->lowerStorage($spacecraft, $commodity, $diff);
            $this->storageManager->upperStorage($station, $commodity, $diff);

            $msg = _('Es wurden %d %s von der %s transferiert');
        }

        return sprintf(
            $msg,
            $diff,
            $commodity->getName(),
            $spacecraft->getName()
        );
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
