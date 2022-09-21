<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\ShowStatistics;

use Amenadiel\JpGraph\Graph\Graph;
use Amenadiel\JpGraph\Plot\LinePlot;
use IntlDateFormatter;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Database\Lib\PlotInfo;
use Stu\Orm\Repository\GameTurnStatsRepositoryInterface;

final class ShowStatistics implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_STATISTICS';

    private const ENTRY_COUNT = 10;

    private int $minY;
    private int $maxY;

    private GameTurnStatsRepositoryInterface $gameTurnStatsRepository;

    public function __construct(
        GameTurnStatsRepositoryInterface $gameTurnStatsRepository
    ) {
        $this->gameTurnStatsRepository = $gameTurnStatsRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $imageInfos = [
            'Spieleranzahl' => [new PlotInfo('getUserCount'), new PlotInfo('getLogins24h', 'yellow', 'yellow@0.5', 'aktiv letzte 24h')],
            'Spieler im Urlaub' => [new PlotInfo('getVacationCount')],
            'Schiffanzahl' => [new PlotInfo('getShipCount'), new PlotInfo('getShipCountManned', 'yellow', 'yellow@0.5', 'bemannt')],
            'KN-Beiträge' => [new PlotInfo('getKnCount')],
            'Geflogene Felder letzte 24h' => [new PlotInfo('getFlightSig24h'), new PlotInfo('getFlightSigSystem24h', 'yellow', 'yellow@0.5', 'System')]
        ];

        $imageSources = $this->createImagesSources($imageInfos);

        $game->appendNavigationPart(
            'database.php',
            _('Datenbank')
        );
        $game->appendNavigationPart(
            sprintf(
                'database.php?%s=1',
                static::VIEW_IDENTIFIER,
            ),
            _('Statistiken')
        );
        $game->setPageTitle(_('/ Statistiken'));
        $game->setTemplateFile('html/statistics.xhtml');

        $game->setTemplateVar('GRAPHS', $imageSources);
    }

    private function createImagesSources(array $imageInfos): array
    {
        $stats = array_reverse($this->gameTurnStatsRepository->getLatestStats(self::ENTRY_COUNT));

        $imageSources = [];

        foreach ($imageInfos as $title => $plotInfos) {
            $imageSources[] = $this->createImageSrc($stats, $plotInfos, $title);
        }

        return $imageSources;
    }

    private function createImageSrc(array $stats, array $plotInfos, string $title): string
    {
        $this->minY = PHP_INT_MAX;
        $this->maxY = 0;

        $datax = $this->createDataX($stats);
        $plots = $this->createPlots($datax, $plotInfos, $stats);

        // Setup the basic graph
        $__width  = 400;
        $__height = 300;
        $graph    = new Graph($__width, $__height);
        $graph->SetMargin(70, 30, 30, 90);
        $graph->SetMarginColor('black');
        $graph->tabtitle->Set($title);
        $graph->tabtitle->SetFont(FF_ARIAL, FS_BOLD, 10);
        $graph->tabtitle->SetColor('white', 'black', 'white');
        $graph->img->SetAntiAliasing(false);
        $graph->SetColor('black');
        $graph->SetBox(false);
        $graph->SetFrame(true);
        $graph->FillMarginArea();
        $graph->img->SetAntiAliasing();
        $graph->SetScale('intint', $this->minY, $this->maxY, $datax[0], $datax[count($datax) - 1]);

        // configure axis
        $this->configureXAxis($graph, $stats);
        $this->configureYAxis($graph);

        $graph->ygrid->SetFill(true, '#121220@0.5', '#121220@0.6');
        $graph->ygrid->Show();

        // Add lineplots to the graph
        foreach ($plots as $plot) {
            $graph->Add($plot);
        }

        // Adjust the legend 
        $graph->legend->SetPos(0.05, 0.01, "top", "right");
        $graph->legend->SetColor('white', 'white');
        $graph->legend->SetFillColor('black');
        $graph->legend->SetFont(FF_ARIAL, FS_BOLD, 8);

        return $this->graphInSrc($graph);
    }

    private function createDataX(array $stats): array
    {
        $datax = [];

        foreach ($stats as $stat) {
            $datax[] = $stat->getTurn()->getStart();
        }

        return $datax;
    }

    private function createDataY(string $method, array $stats): array
    {
        $datay = [];

        foreach ($stats as $stat) {
            $y = $stat->$method();
            $datay[] = $y;

            $this->minY = min($this->minY, $y);
            $this->maxY = max($this->maxY, $y);
        }

        return $datay;
    }

    private function createPlots(array $datax, array $plotInfos, array $stats): array
    {
        $plots = [];

        foreach ($plotInfos as $plotInfo) {
            $datay = $this->createDataY($plotInfo->method, $stats);

            // Create the line
            $plot = new LinePlot($datay, $datax);
            $plot->SetColor($plotInfo->lineColor);

            // Set the fill color partly transparent
            $plot->SetFillColor($plotInfo->fillColor);

            if ($plotInfo->legend !== null) {
                $plot->SetLegend($plotInfo->legend);
            }

            $plots[] = $plot;
        }

        return $plots;
    }

    private function configureXAxis($graph, array $stats): void
    {
        $fmt = new IntlDateFormatter(
            'de-DE',
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            'Europe/Berlin',
            IntlDateFormatter::GREGORIAN,
            'eee, d.MM. H\'h\''
        );

        $tickPositions = [];
        $tickLabels = [];

        foreach ($stats as $stat) {
            $x = $stat->getTurn()->getStart();

            $tickPositions[] = $x;
            $tickLabels[] = $fmt->format((int)$x);
        }

        $graph->xaxis->SetLabelAngle(45);
        $graph->xaxis->SetPos('min');
        $graph->xaxis->SetMajTickPositions($tickPositions, $tickLabels);
        $graph->xaxis->SetFont(FF_ARIAL, FS_NORMAL, 8);
        $graph->xaxis->SetColor('white', 'white');
    }

    private function configureYAxis($graph): void
    {
        if ($this->maxY - $this->minY < 10) {
            $yGrace = 50;
        } else {
            $yGrace = 10;
        }

        $graph->yaxis->scale->SetGrace($yGrace, $yGrace);
        $graph->yaxis->SetFont(FF_ARIAL, FS_NORMAL, 8);
        $graph->yaxis->SetColor('white', 'white');
    }

    private function graphInSrc($graph): string
    {
        $img = $graph->Stroke(_IMG_HANDLER);
        ob_start();
        imagepng($img);
        $img_data = ob_get_contents();
        ob_end_clean();

        return '<img src="data:image/png;base64,' . base64_encode($img_data) . '"/>';
    }
}
