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

        // The callback that converts timestamp to minutes and seconds
        $TimeCallback = function ($aVal) {
            $fmt = new IntlDateFormatter(
                'de-DE',
                IntlDateFormatter::FULL,
                IntlDateFormatter::FULL,
                'Europe/Berlin',
                IntlDateFormatter::GREGORIAN,
                'D, d.m. H\h'
            );

            return $fmt->format((int)$aVal);
            // date('D, d.m. H\h', (int)$aVal);
        };


        $datax = [];
        $datay = [];
        $minY = PHP_INT_MAX;
        $maxY = 0;
        foreach ($stats as $stat) {
            $datax[] = $stat->getTurn()->getStart();
            $y = $stat->getFlightSig24h();
            $datay[] = $y;

            $minY = min($minY, $y);
            $maxY = max($maxY, $y);
        }

        // Setup the basic graph
        $__width  = 500;
        $__height = 350;
        $graph    = new Graph($__width, $__height);
        $graph->SetMargin(70, 30, 30, 90);
        $graph->title->Set('Flugsignaturen 24h');
        $graph->SetAlphaBlending();

        // Setup a manual x-scale (We leave the sentinels for the
        // Y-axis at 0 which will then autoscale the Y-axis.)
        // We could also use autoscaling for the x-axis but then it
        // probably will start a little bit earlier than the first value
        // to make the first value an even number as it sees the timestamp
        // as an normal integer value.
        $graph->SetScale('intlin', $minY, $maxY, $datax[0], $datax[count($datax) - 1]);

        // Setup the x-axis with a format callback to convert the timestamp
        // to a user readable time
        $graph->xaxis->SetLabelFormatCallback($TimeCallback);
        $graph->xaxis->SetLabelAngle(45);

        // Create the line
        $p1 = new LinePlot($datay, $datax);
        $p1->SetColor('blue');

        // Set the fill color partly transparent
        $p1->SetFillColor('blue@0.4');

        // Add lineplot to the graph
        $graph->Add($p1);


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

        $game->setTemplateVar('GRAPH', $this->graphInSrc($graph));
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
