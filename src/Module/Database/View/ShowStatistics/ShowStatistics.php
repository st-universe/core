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
        $imageSources[] = $this->createImageSrc($stats, 'getUserCount', 'Spieleranzahl', $fmt);
        $imageSources[] = $this->createImageSrc($stats, 'getLogins24h', 'Aktive Spieler letzte 24h', $fmt);
        $imageSources[] = $this->createImageSrc($stats, 'getVacationCount', 'Spieler im Urlaub', $fmt);
        $imageSources[] = $this->createImageSrc($stats, 'getShipCount', 'Schiffanzahl', $fmt);
        $imageSources[] = $this->createImageSrc($stats, 'getKnCount', 'KN-Beiträge', $fmt);
        $imageSources[] = $this->createImageSrc($stats, 'getFlightSig24h', 'Geflogene Felder letzte 24h', $fmt);

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

    private function createImageSrc(array $stats, string $method, string $title, IntlDateFormatter $fmt): string
    {
        $tickPositions = [];
        $tickLabels = [];

        $datax = [];
        $datay = [];
        $minY = PHP_INT_MAX;
        $maxY = 0;

        foreach ($stats as $stat) {
            $x = $stat->getTurn()->getStart();
            $y = $stat->$method();
            $datax[] = $x;
            $datay[] = $y;

            $minY = min($minY, $y);
            $maxY = max($maxY, $y);

            $tickPositions[] = $x;
            $tickLabels[] = $fmt->format((int)$x);
        }

        // Setup the basic graph
        $__width  = 400;
        $__height = 300;
        $graph    = new Graph($__width, $__height);
        $graph->SetMargin(70, 0, 30, 90);
        $graph->title->Set($title);
        //$graph->SetAlphaBlending(true);
	$graph->img->SetAntiAliasing(false);
	//$graph->img->SetTransparent('khaki');
        $graph->SetColor('black');
        $graph->SetFrame(false);
	$graph->SetBox(false);
	$graph->img->SetAntiAliasing();
        $graph->SetScale('intint', $minY, $maxY, $datax[0], $datax[count($datax) - 1]);

        $graph->xaxis->SetLabelAngle(45);
        $graph->xaxis->SetPos('min');
        $graph->xaxis->SetMajTickPositions($tickPositions, $tickLabels);
	$graph->xaxis->SetFont(FF_ARIAL,FS_NORMAL,8);
	$graph->xaxis->SetColor('white','white');

        $graph->yaxis->scale->SetGrace(50, 50);
	$graph->xaxis->SetFont(FF_ARIAL,FS_NORMAL,8);
	$graph->xaxis->SetColor('white','white');

	$graph->SetAxisLabelBackground(LABELBKG_XYFULL,'black@0.0','black@0.0','black@0.0','black@0.0');
        // Create the line
        $p1 = new LinePlot($datay, $datax);
        $p1->SetColor('purple');

        // Set the fill color partly transparent
        $p1->SetFillColor('#aa4dec@0.4');

        // Add lineplot to the graph
        $graph->Add($p1);

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
