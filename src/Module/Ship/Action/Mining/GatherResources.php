<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\Mining;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use request;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipStateChangerInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\LocationMiningRepositoryInterface;
use Stu\Orm\Repository\MiningQueueRepositoryInterface;

final class GatherResources implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_GATHER_RESOURCES';

    public function __construct(
        private ShipLoaderInterface $shipLoader,
        private ActivatorDeactivatorHelperInterface $helper,
        private MiningQueueRepositoryInterface $miningQueueRepository,
        private ShipStateChangerInterface $shipStateChanger,
        private EntityManagerInterface $entityManager,
        private LocationMiningRepositoryInterface $locationMiningRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();
        $shipId = request::indInt('id');

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            $shipId,
            $userId
        );

        $ship = $wrapper->get();
        $bussardcollector = $wrapper->getBussardCollectorSystemData();

        if ($bussardcollector === null) {
            throw new SanityCheckException('collector = null ', self::ACTION_IDENTIFIER);
        }

        if ($ship->isWarped()) {
            $game->addInformation("Aktion nicht möglich, Schiff befindet sich im Warp");
            return;
        }

        $chosenLocationId = request::postInt('chosen');

        if ($chosenLocationId === 0) {
            if ($ship->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_BUSSARD_COLLECTOR)) {
                $this->helper->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_BUSSARD_COLLECTOR, $game);
            }

            $miningQueue = $this->miningQueueRepository->getByShip($ship->getId());
            if ($miningQueue !== null) {
                $this->miningQueueRepository->truncateByShipId($ship->getId());
                $game->addInformation("Es werden keine Ressourcen mehr gesammelt");
            }
            $this->shipStateChanger->changeShipState($wrapper, ShipStateEnum::SHIP_STATE_NONE);
            return;
        } else {

            $locationMining = $this->locationMiningRepository->findById($chosenLocationId);
            if ($locationMining === null) {
                throw new SanityCheckException('Invalid location mining ID', self::ACTION_IDENTIFIER);
            }


            if (!$this->helper->activate($wrapper, ShipSystemTypeEnum::SYSTEM_BUSSARD_COLLECTOR, $game)) {
                return;
            }
            $this->shipStateChanger->changeShipState($wrapper, ShipStateEnum::SHIP_STATE_GATHER_RESOURCES);


            $miningqueue = $this->miningQueueRepository->prototype();
            $miningqueue->setShip($ship);
            $miningqueue->setLocationMining($locationMining);
            $this->miningQueueRepository->save($miningqueue);

            $this->entityManager->flush();

            $game->addInformationf(
                "Ressourcen werden gesammelt",
            );
        }
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
