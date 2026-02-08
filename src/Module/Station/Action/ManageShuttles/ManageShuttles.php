<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\ManageShuttles;

use request;
use RuntimeException;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Colony\Lib\ShuttleManagementItem;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Spacecraft\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Station\Lib\StationLoaderInterface;
use Stu\Module\Station\View\ShowShipManagement\ShowShipManagement;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\Station;
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

    #[\Override]
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

        $game->getInfo()->addInformationWrapper($informations);

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
        Spacecraft $spacecraft,
        Station $station
    ): string {
        $commodity = $this->commodityRepository->find($commodityId);
        if ($commodity === null) {
            throw new RuntimeException(sprintf('commodityId %d does not exist', $commodityId));
        }
        $diff = abs($wanted - $current);

        if ($current < $wanted) {
            $availableSpace = $spacecraft->getMaxStorage() - $spacecraft->getStorageSum();
            if ($diff > $availableSpace) {
                if ($availableSpace === 0) {
                    return sprintf(
                        _('Transfer von %d %s zur %s nicht möglich - kein freier Lagerplatz vorhanden'),
                        $diff,
                        $commodity->getName(),
                        $spacecraft->getName()
                    );
                }
                $diff = $availableSpace;
            }

            $this->storageManager->upperStorage($spacecraft, $commodity, $diff);
            $this->storageManager->lowerStorage($station, $commodity, $diff);

            $msg = $diff < abs($wanted - $current)
                ? _('Es wurden nur %d %s zur %s transferiert (Lagerplatz begrenzt)')
                : _('Es wurden %d %s zur %s transferiert');
        } else {
            $availableSpace = $station->getMaxStorage() - $station->getStorageSum();
            if ($diff > $availableSpace) {
                if ($availableSpace === 0) {
                    return sprintf(
                        _('Transfer von %d %s von der %s nicht möglich - kein freier Lagerplatz auf Station vorhanden'),
                        $diff,
                        $commodity->getName(),
                        $spacecraft->getName()
                    );
                }
                $diff = $availableSpace;
            }

            $this->storageManager->lowerStorage($spacecraft, $commodity, $diff);
            $this->storageManager->upperStorage($station, $commodity, $diff);

            $msg = $diff < abs($wanted - $current)
                ? _('Es wurden nur %d %s von der %s transferiert (Lagerplatz begrenzt)')
                : _('Es wurden %d %s von der %s transferiert');
        }

        return sprintf(
            $msg,
            $diff,
            $commodity->getName(),
            $spacecraft->getName()
        );
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
