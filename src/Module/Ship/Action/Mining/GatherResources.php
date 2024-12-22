<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\Mining;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use request;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftStateChangerInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Repository\LocationMiningRepositoryInterface;
use Stu\Orm\Repository\MiningQueueRepositoryInterface;

final class GatherResources implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_GATHER_RESOURCES';

    public function __construct(
        private ShipLoaderInterface $shipLoader,
        private ActivatorDeactivatorHelperInterface $helper,
        private MiningQueueRepositoryInterface $miningQueueRepository,
        private SpacecraftStateChangerInterface $spacecraftStateChanger,
        private EntityManagerInterface $entityManager,
        private LocationMiningRepositoryInterface $locationMiningRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

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
            if ($ship->isSystemHealthy(SpacecraftSystemTypeEnum::BUSSARD_COLLECTOR)) {
                $this->helper->deactivate($wrapper, SpacecraftSystemTypeEnum::BUSSARD_COLLECTOR, $game);
            }

            $miningQueue = $this->miningQueueRepository->getByShip($ship->getId());
            if ($miningQueue !== null) {
                $this->miningQueueRepository->truncateByShipId($ship->getId());
                $game->addInformation("Es werden keine Ressourcen mehr gesammelt");
            }
            $this->spacecraftStateChanger->changeShipState($wrapper, SpacecraftStateEnum::SHIP_STATE_NONE);
            return;
        } else {

            $locationMining = $this->locationMiningRepository->findById($chosenLocationId);
            if ($locationMining === null) {
                throw new SanityCheckException('Invalid location mining ID', self::ACTION_IDENTIFIER);
            }


            if (!$ship->getSystemState(SpacecraftSystemTypeEnum::BUSSARD_COLLECTOR)) {
                if (!$this->helper->activate($wrapper, SpacecraftSystemTypeEnum::BUSSARD_COLLECTOR, $game)) {
                    return;
                }
            } else {
                $miningQueue = $this->miningQueueRepository->getByShip($ship->getId());
                if ($miningQueue !== null) {
                    $this->miningQueueRepository->truncateByShipId($ship->getId());
                }
            }
            $this->spacecraftStateChanger->changeShipState($wrapper, SpacecraftStateEnum::SHIP_STATE_GATHER_RESOURCES);


            $miningqueue = $this->miningQueueRepository->prototype();
            $miningqueue->setShip($ship);
            $miningqueue->setLocationMining($locationMining);
            $this->miningQueueRepository->save($miningqueue);

            $this->entityManager->flush();

            $game->addInformationf(
                sprintf('%s wird gesammelt', $locationMining->getCommodity()->getName()),
            );
        }
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
