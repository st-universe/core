<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ManageOrbitalShuttles;

use request;
use Stu\Module\Ship\Lib\InteractionCheckerInterface;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowOrbitManagement\ShowOrbitManagement;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Module\Colony\Lib\ShuttleManagementItem;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;

final class ManageOrbitalShuttles implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_MANAGE_SHUTTLES';

    private ColonyLoaderInterface $colonyLoader;

    private PrivateMessageSenderInterface $privateMessageSender;

    private ColonyStorageManagerInterface $colonyStorageManager;

    private ShipStorageManagerInterface $shipStorageManager;

    private CommodityRepositoryInterface $commodityRepository;

    private ShipLoaderInterface $shipLoader;

    private InteractionCheckerInterface $interactionChecker;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        PrivateMessageSenderInterface $privateMessageSender,
        ColonyStorageManagerInterface $colonyStorageManager,
        ShipStorageManagerInterface $shipStorageManager,
        CommodityRepositoryInterface $commodityRepository,
        ShipLoaderInterface $shipLoader,
        InteractionCheckerInterface $interactionChecker
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->privateMessageSender = $privateMessageSender;
        $this->colonyStorageManager = $colonyStorageManager;
        $this->shipStorageManager = $shipStorageManager;
        $this->commodityRepository = $commodityRepository;
        $this->shipLoader = $shipLoader;
        $this->interactionChecker = $interactionChecker;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowOrbitManagement::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $userId = $user->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $userId
        );

        $wrapper = $this->shipLoader->find(request::indInt('sid'));
        if ($wrapper === null) {
            return;
        }

        $ship = $wrapper->get();

        if (!$this->interactionChecker->checkColonyPosition($colony, $ship)) {
            return;
        }

        $isForeignShip = $userId !== $ship->getUser()->getId();

        $commodities = request::postArray('shuttles');
        if (empty($commodities)) {
            return;
        }

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

        $msgArray = [];

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
                $msgArray[] = $this->transferShuttles(
                    (int)$commodityId,
                    $smi->getCurrentLoad(),
                    $wantedCount,
                    $ship,
                    $colony
                );
            }
        }

        $game->addInformationMerge($msgArray);

        if ($isForeignShip && !empty($msgArray)) {
            $pm = sprintf(
                _('Die Kolonie %s des Spielers %s transferiert Shuttles in Sektor %d|%d') . "\n",
                $colony->getName(),
                $colony->getUser()->getUserName(),
                $ship->getPosX(),
                $ship->getPosY()
            );
            foreach ($msgArray as $value) {
                $pm .= $value . "\n";
            }
            $href = sprintf(_('ship.php?SHOW_SHIP=1&id=%d'), $ship->getId());
            $this->privateMessageSender->send(
                $userId,
                $ship->getUser()->getId(),
                $pm,
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
                $href
            );
        }
    }

    private function transferShuttles(
        int $commodityId,
        int $current,
        int $wanted,
        ShipInterface $ship,
        ColonyInterface $colony
    ): string {
        $commodity = $this->commodityRepository->find($commodityId);
        $diff = abs($wanted - $current);

        if ($current < $wanted) {
            $this->shipStorageManager->upperStorage($ship, $commodity, $diff);
            $this->colonyStorageManager->lowerStorage($colony, $commodity, $diff);

            $msg = _('Es wurden %d %s zur %s transferiert');
        } else {
            $this->shipStorageManager->lowerStorage($ship, $commodity, $diff);
            $this->colonyStorageManager->upperStorage($colony, $commodity, $diff);

            $msg = _('Es wurden %d %s von der %s transferiert');
        }

        return sprintf(
            $msg,
            $diff,
            $commodity->getName(),
            $ship->getName()
        );
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
