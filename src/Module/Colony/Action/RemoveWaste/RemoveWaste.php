<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\RemoveWaste;

use request;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class RemoveWaste implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_REMOVE_WASTE';

    public function __construct(
        private ColonyLoaderInterface $colonyLoader,
        private PlanetFieldRepositoryInterface $planetFieldRepository,
        private StorageManagerInterface $storageManager,
        private ColonyRepositoryInterface $colonyRepository,
        private CommodityRepositoryInterface $commodityRepository
    ) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $context->setView(ShowColony::VIEW_IDENTIFIER);

        $userId = $context->getUser()->getId();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $userId
        );

        $commodities = request::postArrayFatal('commodity');

        if ($this->planetFieldRepository->getCountByColonyAndBuildingFunctionAndState(
            $colony,
            [BuildingFunctionEnum::WAREHOUSE],
            [0, 1]
        ) === 0) {
            return;
        }

        $storages = $colony->getStorage();

        $wasted = [];
        foreach ($commodities as $commodityId => $count) {

            $storage = $storages->get((int)$commodityId);
            if ($storage === null) {
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

            if ($count > $storage->getAmount()) {
                $count = $storage->getAmount();
            }

            $this->storageManager->lowerStorage($colony, $commodity, $count);
            $wasted[] = sprintf('%d %s', $count, $commodity->getName());
        }
        $this->colonyRepository->save($colony);
        $context->getInfo()->addInformation(_('Die folgenden Waren wurden entsorgt:'));
        foreach ($wasted as $msg) {
            $context->getInfo()->addInformation($msg);
        }
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
