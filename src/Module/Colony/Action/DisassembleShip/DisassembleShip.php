<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\DisassembleShip;

use request;

use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Component\Ship\ShipEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Colony\View\ShowShipDisassembly\ShowShipDisassembly;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;

final class DisassembleShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DISASSEMBLE_SHIP';

    private ColonyLoaderInterface $colonyLoader;

    private ShipLoaderInterface $shipLoader;

    private ColonyRepositoryInterface $colonyRepository;

    private ShipRemoverInterface $shipRemover;

    private ColonyStorageManagerInterface $colonyStorageManager;

    private CommodityRepositoryInterface $commodityRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShipLoaderInterface $shipLoader,
        ColonyRepositoryInterface $colonyRepository,
        ShipRemoverInterface $shipRemover,
        ColonyStorageManagerInterface $colonyStorageManager,
        CommodityRepositoryInterface $commodityRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->shipLoader = $shipLoader;
        $this->colonyRepository = $colonyRepository;
        $this->shipRemover = $shipRemover;
        $this->colonyStorageManager = $colonyStorageManager;
        $this->commodityRepository = $commodityRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShipDisassembly::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $userId
        );

        if ($colony->getEps() < 20) {
            $game->addInformation(_('Zur Demontage des Schiffes wird 20 Energie benötigt'));
            return;
        }

        $colony->lowerEps(20);

        $this->colonyRepository->save($colony);

        $ship_id = request::getIntFatal('ship_id');

        $ship = $this->shipLoader->getByIdAndUser((int) $ship_id, $userId);
        $this->retrieveSomeIntactModules($ship, $colony, $game);
        $this->retrieveWarpcoreLoad($ship, $colony, $game);

        $this->shipRemover->remove($ship);

        $game->addInformationf(_('Das Schiff wurde demontiert'));
    }

    private function retrieveSomeIntactModules($ship, $colony, $game): void
    {
        $intactModules = [];

        foreach ($ship->getSystems() as $system) {
            if (
                $system->getModule() !== null
                && $system->getStatus() == 100
            ) {
                $module = $system->getModule();

                if (!array_key_exists($module->getId(), $intactModules)) {
                    $intactModules[$module->getId()] = $module;
                }
            }
        }

        $maxStorage = $colony->getMaxStorage();

        //retrieve 50% of all intact modules
        $recycleCount = (int) ceil(count($intactModules) / 2);
        for ($i = 1; $i <= $recycleCount; $i++) {
            if ($colony->getStorageSum() >= $maxStorage) {
                $game->addInformationf(_('Kein Lagerraum frei um Module zu recyclen!'));
                break;
            }

            $module = $intactModules[array_rand($intactModules)];
            unset($intactModules[$module->getId()]);

            $this->colonyStorageManager->upperStorage(
                $colony,
                $module->getCommodity(),
                1
            );

            $game->addInformationf(sprintf(_('Folgendes Modul konnte recycelt werden: %s'), $module->getName()));
        }
    }

    private function retrieveWarpcoreLoad(ShipInterface $ship, $colony, $game): void
    {
        if (!$ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_WARPCORE)) {
            return;
        }
        $loads = (int) floor($ship->getReactorLoad() / ShipEnum::WARPCORE_LOAD);

        if ($loads < 1) {
            return;
        }

        $maxStorage = $colony->getMaxStorage();

        foreach (ShipEnum::WARPCORE_LOAD_COST as $commodityId => $loadCost) {
            if ($colony->getStorageSum() >= $maxStorage) {
                $game->addInformationf(_('Kein Lagerraum frei um Warpkern-Mix zu sichern!'));
                break;
            }

            $amount = (int) $loads * $loadCost;
            if ($maxStorage - $colony->getStorageSum() < $amount) {
                $amount = $maxStorage - $colony->getStorageSum();
            }

            $commodity = $this->commodityRepository->find($commodityId);
            $this->colonyStorageManager->upperStorage(
                $colony,
                $commodity,
                $amount
            );

            $game->addInformationf(sprintf(_('%d Einheiten folgender Ware konnten recycelt werden: %s'), $amount, $commodity->getName()));
        }
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
