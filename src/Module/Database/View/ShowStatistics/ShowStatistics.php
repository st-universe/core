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
        $imageSources[] = $this->createImageSrc($stats, ['purple' => 'getUserCount'], 'Spieleranzahl', $fmt);
        $imageSources[] = $this->createImageSrc($stats, ['purple' => 'getLogins24h'], 'Aktive Spieler letzte 24h', $fmt);
        $imageSources[] = $this->createImageSrc($stats, ['purple' => 'getVacationCount'], 'Spieler im Urlaub', $fmt);
        $imageSources[] = $this->createImageSrc($stats, ['purple' => 'getShipCount', 'blue' => 'getShipCountManned'], 'Schiffanzahl', $fmt);
        $imageSources[] = $this->createImageSrc($stats, ['purple' => 'getKnCount'], 'KN-Beiträge', $fmt);
        $imageSources[] = $this->createImageSrc($stats, ['purple' => 'getFlightSig24h'], 'Geflogene Felder letzte 24h', $fmt);

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

    private function createImageSrc(array $stats, array $plotData, string $title, IntlDateFormatter $fmt): string
    {
        $tickPositions = [];
        $tickLabels = [];

        $datax = [];
        $datay = [];
        $minY = PHP_INT_MAX;
        $maxY = 0;

        foreach ($stats as $stat) {
            $x = $stat->getTurn()->getStart();
            $datax[] = $x;
            $tickPositions[] = $x;
            $tickLabels[] = $fmt->format((int)$x);

            // collect data for multiple plot lines
            foreach ($plotData as $key => $method) {
                if (!array_key_exists($key, $datay)) {
                    $datay[$key] = [];
                }

                $y = $stat->$method();
                $dataArray = $datay[$key];
                $dataArray[] = $y;

                $minY = min($minY, $y);
                $maxY = max($maxY, $y);
            }
        }

        // Setup the basic graph
        $__width  = 400;
        $__height = 300;
        $graph    = new Graph($__width, $__height);
        $graph->SetMargin(70, 30, 30, 90);
        $graph->title->Set($title);
        $graph->SetAlphaBlending(true);
        $graph->SetFrame(false);
        //$graph->img->SetTransparent('green');
        //$graph->ygrid->Show(false, false);
        $graph->SetColor('black@0.0');
        $graph->SetMarginColor('black@0.0');

        $graph->SetScale('intint', $minY, $maxY, $datax[0], $datax[count($datax) - 1]);

        $graph->xaxis->SetLabelAngle(45);
        $graph->xaxis->SetPos('min');
        $graph->xaxis->SetMajTickPositions($tickPositions, $tickLabels);

        $graph->yaxis->scale->SetGrace(50, 50);

        $graph->SetAxisLabelBackground(LABELBKG_XYFULL, 'black@0.0', 'black@0.0', 'black@0.0', 'black@0.0');

        // Create the lines
        foreach ($datay as $color => $dataArray) {
            $plot = new LinePlot($dataArray, $datax);
            $plot->SetColor($color);

            // Set the fill color partly transparent
            $plot->SetFillColor('#aa4dec@0.4');

            // Add lineplot to the graph
            $graph->Add($plot);
        }

        return $this->graphInSrc($graph);
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
