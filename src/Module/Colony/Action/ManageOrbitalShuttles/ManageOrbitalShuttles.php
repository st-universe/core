<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ManageOrbitalShuttles;

use request;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\Lib\ShuttleManagementItem;
use Stu\Module\Colony\View\ShowOrbitManagement\ShowOrbitManagement;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;

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

        $colony = $this->colonyLoader->loadWithOwnerValidation(
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
        if ($commodities === []) {
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

        $informations = new InformationWrapper();

        foreach ($commodities as $commodityId) {
            if (!is_numeric($commodityId)) {
                return;
            }

            $commodity = $this->commodityRepository->find((int)$commodityId);

            if ($commodity === null) {
                return;
            }

            if (!$commodity->isShuttle()) {
                return;
            }

            $wantedCount = (int)$shuttlecount[$commodityId];

            $smi = $shuttles[(int)$commodityId];

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

        $game->addInformationWrapper($informations);

        if ($isForeignShip && $informations->getInformations() !== []) {
            $pm = sprintf(
                _("Die Kolonie %s des Spielers %s transferiert Shuttles in Sektor %d|%d\n%s"),
                $colony->getName(),
                $colony->getUser()->getName(),
                $ship->getPosX(),
                $ship->getPosY(),
                $informations->getInformationsAsString()
            );
            $href = sprintf('ship.php?%s=1&id=%d', ShowShip::VIEW_IDENTIFIER, $ship->getId());
            $this->privateMessageSender->send(
                $userId,
                $ship->getUser()->getId(),
                $pm,
                PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
                $href
            );
        }
    }

    private function transferShuttles(
        CommodityInterface $commodity,
        int $current,
        int $wanted,
        ShipInterface $ship,
        ColonyInterface $colony,
        InformationWrapper $informations
    ): void {
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

        $informations->addInformation(sprintf(
            $msg,
            $diff,
            $commodity->getName(),
            $ship->getName()
        ));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
