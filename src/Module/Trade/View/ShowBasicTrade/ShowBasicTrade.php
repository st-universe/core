<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowBasicTrade;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Trade\Lib\BasicTradeAccountWrapperInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Orm\Entity\BasicTrade;
use Stu\Orm\Entity\TradePost;
use Stu\Orm\Repository\BasicTradeRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class ShowBasicTrade implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_BASIC_TRADE';
    private const int DEFAULT_CHART_TRADE_COUNT = 50;
    private const int Y_AXIS_VALUE_DIVISOR = 100;
    private const int CHART_WIDTH = 560;
    private const int CHART_HEIGHT = 130;

    public function __construct(private BasicTradeRepositoryInterface $basicTradeRepository, private TradePostRepositoryInterface $tradePostRepository, private TradeLibFactoryInterface $tradeLibFactory) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $basicTrades = $this->basicTradeRepository->getBasicTrades($userId);
        $tradeCountsByChart = $this->getTradeCountsByChart($basicTrades);
        $maxChartTradeCount = $this->getMaximumChartTradeCount($tradeCountsByChart);
        $chartTradeCount = $this->getChartTradeCount($maxChartTradeCount);

        $basicTradeAccounts = array_map(
            fn (TradePost $tradePost): BasicTradeAccountWrapperInterface => $this->tradeLibFactory->createBasicTradeAccountWrapper($tradePost, $basicTrades, $userId),
            $this->tradePostRepository->getByUserLicenseOnlyNPC($userId)
        );

        $game->appendNavigationPart(
            'trade.php',
            _('Handel')
        );
        $game->appendNavigationPart(
            sprintf('trade.php?%s=1', self::VIEW_IDENTIFIER),
            _('Basishandel')
        );
        $game->setPageTitle(_('/ Handel'));
        $game->setViewTemplate('html/trade/basictrades.twig');

        $game->setTemplateVar('ACCOUNTS', $basicTradeAccounts);
        $game->setTemplateVar('BASIC_TRADE_CHARTS', $this->createBasicTradeCharts($basicTrades, $tradeCountsByChart, $chartTradeCount));
        $game->setTemplateVar('CHART_TRADE_COUNT', $chartTradeCount);
        $game->setTemplateVar('MAX_CHART_TRADE_COUNT', $maxChartTradeCount);
    }

    private function getChartTradeCount(int $maxChartTradeCount): int
    {
        $chartTradeCount = request::indInt('chart_trade_count');
        $maxChartTradeCount = max(1, $maxChartTradeCount);

        if ($chartTradeCount <= 0) {
            return min(self::DEFAULT_CHART_TRADE_COUNT, $maxChartTradeCount);
        }

        return min($chartTradeCount, $maxChartTradeCount);
    }

    /**
     * @param array<BasicTrade> $basicTrades
     * @return array<string, int>
     */
    private function getTradeCountsByChart(array $basicTrades): array
    {
        $tradeCountsByChart = [];

        foreach ($basicTrades as $basicTrade) {
            $tradeCountsByChart[$this->getChartKey($basicTrade)] = $this->basicTradeRepository->getTradeCount($basicTrade);
        }

        return $tradeCountsByChart;
    }

    /**
     * @param array<string, int> $tradeCountsByChart
     */
    private function getMaximumChartTradeCount(array $tradeCountsByChart): int
    {
        if ($tradeCountsByChart === []) {
            return self::DEFAULT_CHART_TRADE_COUNT;
        }

        return max($tradeCountsByChart);
    }

    /**
     * @param array<BasicTrade> $basicTrades
     * @param array<string, int> $tradeCountsByChart
     * @return array<int, array{
     *     commodityId: int,
     *     commodityName: string,
     *     totalTradeCount: int,
     *     valueCount: int,
     *     axisTopValue: int,
     *     axisMiddleValue: int,
     *     axisBottomValue: int,
     *     polylinePoints: string,
     *     xTicks: array<int, array{
     *         positionPercent: float,
     *         label: string,
     *         align: string
     *     }>
     * }>
     */
    private function createBasicTradeCharts(array $basicTrades, array $tradeCountsByChart, int $chartTradeCount): array
    {
        $charts = [];

        foreach ($basicTrades as $basicTrade) {
            $latestRates = array_reverse($this->basicTradeRepository->getLatestRatesByAmount($basicTrade, $chartTradeCount));

            if ($latestRates === []) {
                continue;
            }

            $values = array_map(
                static fn (BasicTrade $trade): int => $trade->getValue(),
                $latestRates
            );

            $valueCount = count($values);
            $minValue = min($values);
            $maxValue = max($values);

            $charts[] = [
                'commodityId' => $basicTrade->getCommodity()->getId(),
                'commodityName' => $basicTrade->getCommodity()->getName(),
                'totalTradeCount' => $tradeCountsByChart[$this->getChartKey($basicTrade)] ?? 0,
                'valueCount' => $valueCount,
                'axisTopValue' => $this->formatYAxisValue($maxValue),
                'axisMiddleValue' => $this->formatYAxisValue(($maxValue + $minValue) / 2),
                'axisBottomValue' => $this->formatYAxisValue($minValue),
                'polylinePoints' => $this->buildPolylinePoints($values),
                'xTicks' => $this->buildXAxisTicks($valueCount)
            ];
        }

        return $charts;
    }

    /**
     * @param array<int, int> $values
     */
    private function buildPolylinePoints(array $values): string
    {
        if ($values === []) {
            return '';
        }

        $count = count($values);
        $width = self::CHART_WIDTH;
        $height = self::CHART_HEIGHT;
        $maxX = $width - 1;
        $maxY = $height - 1;
        $minValue = min($values);
        $maxValue = max($values);
        $range = max(1, $maxValue - $minValue);
        $points = [];

        foreach ($values as $index => $value) {
            $x = $count > 1 ? (int)round($index * $maxX / ($count - 1)) : 0;
            $y = $maxValue !== $minValue
                ? (int)round($maxY - (($value - $minValue) * $maxY / $range))
                : (int)round($maxY / 2);
            $points[] = sprintf('%d,%d', $x, $y);
        }

        if ($count === 1) {
            $points[] = sprintf('%d,%d', $maxX, (int)round($maxY / 2));
        }

        return implode(' ', $points);
    }

    /**
     * @return array<int, array{
     *     positionPercent: float,
     *     label: string,
     *     align: string
     * }>
     */
    private function buildXAxisTicks(int $valueCount): array
    {
        if ($valueCount <= 0) {
            return [];
        }

        $tickAmount = $this->getXAxisTickAmount($valueCount);
        $result = [];

        for ($i = 0; $i < $tickAmount; $i++) {
            $ratio = $tickAmount === 1 ? 1.0 : $i / ($tickAmount - 1);
            $tradeNumber = (int) round($valueCount - ($ratio * ($valueCount - 1)));
            $align = 'center';

            if ($i === 0) {
                $tradeNumber = $valueCount;
                $align = 'left';
            }

            if ($i === $tickAmount - 1) {
                $tradeNumber = 1;
                $align = 'right';
            }

            $result[] = [
                'positionPercent' => (float) round($ratio * 100, 3),
                'label' => $i === 0
                    ? sprintf('%d. letzte', $tradeNumber)
                    : (string) $tradeNumber,
                'align' => $align
            ];
        }

        return $result;
    }

    private function getXAxisTickAmount(int $valueCount): int
    {
        if ($valueCount >= 15) {
            return 5;
        }

        if ($valueCount >= 7) {
            return 4;
        }

        if ($valueCount >= 4) {
            return 3;
        }

        return 2;
    }

    private function formatYAxisValue(int|float $value): int
    {
        return (int) floor($value / self::Y_AXIS_VALUE_DIVISOR);
    }

    private function getChartKey(BasicTrade $basicTrade): string
    {
        return sprintf(
            '%d_%d',
            $basicTrade->getFaction()->getId(),
            $basicTrade->getCommodity()->getId()
        );
    }
}
