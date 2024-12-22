<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\TransformResources;

use Override;
use request;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Station\Lib\StationLoaderInterface;
use Stu\Orm\Repository\BuildingCommodityRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;

final class TransformResources implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_TRANSFORM_RESOURCES';

    public function __construct(
        private StationLoaderInterface $stationLoader,
        private ActivatorDeactivatorHelperInterface $helper,
        private BuildingCommodityRepositoryInterface $buildingCommodityRepository,
        private CommodityRepositoryInterface $commodityRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();
        $shipId = request::indInt('id');

        $wrapper = $this->stationLoader->getWrapperByIdAndUser(
            $shipId,
            $userId
        );

        $ship = $wrapper->get();
        $aggregationsystem = $wrapper->getAggregationSystemSystemData();

        if ($aggregationsystem === null) {
            throw new SanityCheckException('collector = null ', self::ACTION_IDENTIFIER);
        }

        $commodityId = request::postInt('chosen');



        if ($commodityId === 0) {
            if ($ship->isSystemHealthy(SpacecraftSystemTypeEnum::AGGREGATION_SYSTEM)) {
                $this->helper->deactivate($wrapper, SpacecraftSystemTypeEnum::AGGREGATION_SYSTEM, $game);
                $aggregationsystem->setCommodityId($commodityId)->update();
            }
            return;
        } else {

            if (
                !$ship->getSystemState(SpacecraftSystemTypeEnum::AGGREGATION_SYSTEM)
                && !$this->helper->activate($wrapper, SpacecraftSystemTypeEnum::AGGREGATION_SYSTEM, $game)
            ) {
                return;
            }

            $canProduce = false;
            foreach (CommodityTypeEnum::COMMODITY_CONVERSIONS as $conversion) {
                if ($conversion[0] === $commodityId) {
                    $targetCommodityId = $conversion[1];
                    if ($this->buildingCommodityRepository->canProduceCommodity($userId, $targetCommodityId)) {
                        $canProduce = true;
                        $sourceCommodity = $this->commodityRepository->find($conversion[0]);
                        $targetCommodity = $this->commodityRepository->find($conversion[1]);
                        break;
                    }
                }
            }

            if (!$canProduce) {
                $game->addInformation("Diese Ressource kann nicht produziert werden");
                return;
            }

            $aggregationsystem->setCommodityId($commodityId)->update();
            if ($sourceCommodity &&  $targetCommodity) {
                $game->addInformationf(
                    sprintf(
                        "%s wird in %s umgewandelt",
                        $sourceCommodity->getName(),
                        $targetCommodity->getName()
                    )
                );
            }
        }
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
