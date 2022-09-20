<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\ShowStatistics;

use Amenadiel\JpGraph\Graph\Graph;
use Amenadiel\JpGraph\Plot\LinePlot;
use IntlDateFormatter;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\GameTurnStatsRepositoryInterface;

final class ShowStatistics implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_STATISTICS';

    private const ENTRY_COUNT = 10;

    private GameTurnStatsRepositoryInterface $gameTurnStatsRepository;

    public function __construct(
        GameTurnStatsRepositoryInterface $gameTurnStatsRepository
    ) {
        $this->gameTurnStatsRepository = $gameTurnStatsRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $stats = array_reverse($this->gameTurnStatsRepository->getLatestStats(self::ENTRY_COUNT));

        $fmt = new IntlDateFormatter(
            'de-DE',
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            'Europe/Berlin',
            IntlDateFormatter::GREGORIAN,
            'eee, d.MM. H\'h\''
        );

        $imageSources = [];
        $imageSources[] = $this->createImageSrc($stats, ['purple' => 'getUserCount'], 'Spieleranzahl');
        $imageSources[] = $this->createImageSrc($stats, ['purple' => 'getLogins24h'], 'Aktive Spieler letzte 24h');
        $imageSources[] = $this->createImageSrc($stats, ['purple' => 'getVacationCount'], 'Spieler im Urlaub');
        $imageSources[] = $this->createImageSrc($stats, ['purple' => 'getShipCount', 'blue' => 'getShipCountManned'], 'Schiffanzahl');
        $imageSources[] = $this->createImageSrc($stats, ['purple' => 'getKnCount'], 'KN-Beiträge');
        $imageSources[] = $this->createImageSrc($stats, ['purple' => 'getFlightSig24h'], 'Geflogene Felder letzte 24h');

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

    private function createImageSrc(array $stats, array $plotInfo, string $title): string
    {
        // Setup the basic graph
        $__width  = 400;
        $__height = 300;
        $graph    = new Graph($__width, $__height);
        $graph->SetMargin(70, 0, 30, 90);
        $graph->SetMarginColor('black');
        $graph->title->SetFont(FF_ARIAL, FS_BOLD, 10);
        $graph->tabtitle->Set($title);
        $graph->tabtitle->SetColor('white', 'black', 'black');
        $graph->tabtitle->SetTabAlign('center');
        $graph->tabtitle->SetCorner(0);
        $graph->SetTitleBackground(
            'black',
            TITLEBKG_STYLE1,
            TITLEBKG_FRAME_NONE,
            'black',
            0,
            0,
            true
        );
        //$graph->SetAlphaBlending(true);
        $graph->img->SetAntiAliasing(false);
        //$graph->img->SetTransparent('khaki');
        $graph->SetColor('black');
        $graph->SetFrame(false);
        $graph->SetBox(false);
        $graph->img->SetAntiAliasing();

        $datax = $this->getDataX($stats);

        // Create the lines
        foreach ($plotInfo as $color => $method) {
            $plot = $this->createPlot($stats, $datax, $color, $method);

            // Add lineplot to the graph
            $graph->Add($plot);
        }

        //set the scale
        $graph->SetScale('intint', $this->minY, $this->maxY, $datax[0], $datax[count($datax) - 1]);

        // configure X-axis
        $this->configureXAxis($stats, $graph);

        //configure Y-axis
        $graph->yaxis->SetFont(FF_ARIAL, FS_NORMAL, 8);
        $graph->yaxis->SetColor('white', 'white');
        $graph->yaxis->scale->SetGrace(50, 50);

        $graph->ygrid->SetFill(true, 'black@0.95', 'black@0.7');
        $graph->ygrid->Show();


        $graph->SetAxisLabelBackground(LABELBKG_XYFULL, 'black@0.0', 'black@0.0', 'black@0.0', 'black@0.0');

        return $this->graphInSrc($graph);
    }

    private function getDataX(array $stats): array
    {
        $datax = [];

        // collect data for X-Axis
        foreach ($stats as $stat) {
            $datax[] =  $stat->getTurn()->getStart();
        }

        return $datax;
    }

    private function configureXAxis(array $stats, $graph): void
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

        // collect data for X-Axis
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

    private function createPlot(array $stats, $datax, string $color, $method): LinePlot
    {
        $datay = [];

        foreach ($stats as $stat) {
            $y = $stat->$method();
            $datay[] = $y;

            $this->minY = min($this->minY, $y);
            $this->maxY = max($this->maxY, $y);
        }

        // Create the line
        $plot = new LinePlot($datay, $datax);
        $plot->SetColor($color);

        // Set the fill color partly transparent
        $plot->SetFillColor('#aa4dec@0.4');

        return $plot;
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
