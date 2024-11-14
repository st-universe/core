<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowAggregationSystem;

use Override;
use request;
use Stu\Exception\SanityCheckException;
use Stu\Component\Faction\FactionEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\BuildingCommodityRepositoryInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;

final class ShowAggregationSystem implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_AGGREGATION_SYSTEM_AJAX';

    public function __construct(
        private ShipLoaderInterface $shipLoader,
        private CommodityRepositoryInterface $commodityRepository,
        private BuildingCommodityRepositoryInterface $buildingCommodityRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId,
            false,
            false
        );

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId,
            false,
            false
        );
        $module = $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_AGGREGATION_SYSTEM)->getModule();

        $game->setPageTitle(_('Aggregationssystem'));
        $game->setMacroInAjaxWindow('html/ship/aggregationsystem.twig');

        $game->setTemplateVar('WRAPPER', $wrapper);

        $aggregationsystem = $wrapper->getAggregationSystemSystemData();
        if ($aggregationsystem === null) {
            throw new SanityCheckException('no aggregation system installed', null, self::VIEW_IDENTIFIER);
        }

        $commodities = CommodityTypeEnum::COMMODITY_CONVERSIONS;
        $mode1Commodities = array_filter($commodities, fn($entry): bool => $entry[4] === 1);
        $mode2Commodities = array_filter($commodities, fn($entry): bool => $entry[4] === 2);


        $mode1Commodities = array_map(fn($entry): array => [
            $this->commodityRepository->find($entry[0]),
            $this->commodityRepository->find($entry[1]),
            $entry[2],
            $entry[3]
        ], $mode1Commodities);

        $mode2Commodities = array_map(fn($entry): array => [
            $this->commodityRepository->find($entry[0]),
            $this->commodityRepository->find($entry[1]),
            $entry[2],
            $entry[3]
        ], $mode2Commodities);

        if ($module && $module->getFactionId() == FactionEnum::FACTION_FERENGI) {
            foreach ($mode1Commodities as &$entry) {
                $entry[2] *= 2;
                $entry[3] *= 2;
            }

            foreach ($mode2Commodities as &$entry) {
                $entry[2] *= 2;
                $entry[3] *= 2;
            }
        }


        $mode1Commodities = array_filter($mode1Commodities, function ($entry) use ($userId): bool {
            return $entry[1] !== null && $this->buildingCommodityRepository->canProduceCommodity($userId, $entry[1]->getId());
        });

        $mode2Commodities = array_filter($mode2Commodities, function ($entry) use ($userId): bool {
            return $entry[1] !== null && $this->buildingCommodityRepository->canProduceCommodity($userId, $entry[1]->getId());
        });

        $chosencommodity = $aggregationsystem->getCommodityId();
        $game->setTemplateVar('MODE1_COMMODITIES', $mode1Commodities);
        $game->setTemplateVar('MODE2_COMMODITIES', $mode2Commodities);
        $game->setTemplateVar('CHOSENCOMMODITY', $chosencommodity);
    }
}
