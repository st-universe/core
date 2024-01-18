<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\RemoveWaste;

use request;
use Stu\Component\Building\BuildingEnum;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class RemoveWaste implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_REMOVE_WASTE';

    private ColonyLoaderInterface $colonyLoader;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private ColonyStorageManagerInterface $colonyStorageManager;

    private ColonyRepositoryInterface $colonyRepository;

    private CommodityRepositoryInterface $commodityRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        PlanetFieldRepositoryInterface $planetFieldRepository,
        ColonyStorageManagerInterface $colonyStorageManager,
        ColonyRepositoryInterface $colonyRepository,
        CommodityRepositoryInterface $commodityRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->planetFieldRepository = $planetFieldRepository;
        $this->colonyStorageManager = $colonyStorageManager;
        $this->colonyRepository = $colonyRepository;
        $this->commodityRepository = $commodityRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowColony::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $userId
        );

        $commodities = request::postArrayFatal('commodity');

        if ($this->planetFieldRepository->getCountByColonyAndBuildingFunctionAndState(
            $colony,
            [BuildingEnum::BUILDING_FUNCTION_WAREHOUSE],
            [0, 1]
        ) === 0) {
            return;
        }

        $storage = $colony->getStorage();

        $wasted = [];
        foreach ($commodities as $commodityId => $count) {
            if (!$storage->containsKey((int)$commodityId)) {
                continue;
            }
            $count = (int)$count;

            if ($count < 1) {
                continue;
            }

            $commodity = $this->commodityRepository->find((int)$commodityId);

            if ($commodity === null) {
                continue;
            }

            $stor = $storage->get((int)$commodityId);

            if ($count > $stor->getAmount()) {
                $count = $stor->getAmount();
            }

            $this->colonyStorageManager->lowerStorage($colony, $commodity, $count);
            $wasted[] = sprintf('%d %s', $count, $commodity->getName());
        }
        $this->colonyRepository->save($colony);
        $game->addInformation(_('Die folgenden Waren wurden entsorgt:'));
        foreach ($wasted as $msg) {
            $game->addInformation($msg);
        }
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
