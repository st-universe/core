<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowSystemSettings;

use RuntimeException;
use Stu\Component\Faction\FactionEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Station\Lib\StationWrapperInterface;
use Stu\Orm\Repository\BuildingCommodityRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;

class AggregrationSystemSettings implements SystemSettingsProviderInterface
{
    public function __construct(
        private CommodityRepositoryInterface $commodityRepository,
        private BuildingCommodityRepositoryInterface $buildingCommodityRepository
    ) {}

    #[\Override]
    public function setTemplateVariables(
        SpacecraftSystemTypeEnum $systemType,
        SpacecraftWrapperInterface $wrapper,
        GameControllerInterface $game
    ): void {
        $userId = $game->getUser()->getId();
        $spacecraft = $wrapper->get();
        $module = $spacecraft->getSpacecraftSystem($systemType)->getModule();

        $game->setMacroInAjaxWindow('html/ship/aggregationsystem.twig');

        if (!$wrapper instanceof StationWrapperInterface) {
            throw new RuntimeException('this should not happen');
        }

        $aggregationsystem = $wrapper->getAggregationSystemSystemData();
        if ($aggregationsystem === null) {
            throw new SanityCheckException(
                'no aggregation system installed',
                null,
                ShowSystemSettings::VIEW_IDENTIFIER
            );
        }

        $commodities = CommodityTypeConstants::COMMODITY_CONVERSIONS;
        $mode1Commodities = array_filter($commodities, fn ($entry): bool => $entry[4] === 1);
        $mode2Commodities = array_filter($commodities, fn ($entry): bool => $entry[4] === 2);

        $mode1Commodities = array_map(fn ($entry): array => [
            $this->commodityRepository->find($entry[0]),
            $this->commodityRepository->find($entry[1]),
            $entry[2],
            $entry[3]
        ], $mode1Commodities);

        $mode2Commodities = array_map(fn ($entry): array => [
            $this->commodityRepository->find($entry[0]),
            $this->commodityRepository->find($entry[1]),
            $entry[2],
            $entry[3]
        ], $mode2Commodities);

        if ($module && $module->getFactionId() == FactionEnum::FACTION_FERENGI) {
            foreach (array_keys($mode1Commodities) as $key) {
                $mode1Commodities[$key][2] *= 2;
                $mode1Commodities[$key][3] *= 2;
            }

            foreach (array_keys($mode2Commodities) as $key) {
                $mode2Commodities[$key][2] *= 2;
                $mode2Commodities[$key][3] *= 2;
            }
        }

        $mode1Commodities = array_filter($mode1Commodities, function (array $entry) use ($userId): bool {
            return $entry[1] !== null
            && $this->buildingCommodityRepository->canProduceCommodity($userId, $entry[1]->getId());
        });

        $mode2Commodities = array_filter($mode2Commodities, function (array $entry) use ($userId): bool {
            return $entry[1] !== null
            && $this->buildingCommodityRepository->canProduceCommodity($userId, $entry[1]->getId());
        });

        $chosencommodity = $aggregationsystem->getCommodityId();
        $game->setTemplateVar('MODE1_COMMODITIES', $mode1Commodities);
        $game->setTemplateVar('MODE2_COMMODITIES', $mode2Commodities);
        $game->setTemplateVar('CHOSENCOMMODITY', $chosencommodity);
    }
}
