<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\ShowStatistics;

use Amenadiel\JpGraph\Graph\Graph;
use Amenadiel\JpGraph\Plot\LinePlot;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ShowStatistics implements ViewControllerInterface
{

    public const VIEW_IDENTIFIER = 'SHOW_STATISTICS';

    private UserRepositoryInterface $userRepository;

    public function __construct(
        UserRepositoryInterface $userRepository
    ) {
        $this->userRepository = $userRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        // The callback that converts timestamp to minutes and seconds
        $TimeCallback = function ($aVal) {
            return date('H:i:s', (int)$aVal);
        };

        // Fake some suitable random data
        $now   = time();
        $datax = [$now];
        for ($i = 0; $i < 360; $i += 10) {
            $datax[] = $now + $i;
        }
        $n     = count($datax);
        $datay = [];
        for ($i = 0; $i < $n; ++$i) {
            $datay[] = rand(30, 150);
        }

        // Setup the basic graph
        $__width  = 324;
        $__height = 250;
        $graph    = new Graph($__width, $__height);
        $graph->SetMargin(40, 40, 30, 70);
        $graph->title->Set('Date: ' . date('Y-m-d', $now));
        $graph->SetAlphaBlending();

        // Setup a manual x-scale (We leave the sentinels for the
        // Y-axis at 0 which will then autoscale the Y-axis.)
        // We could also use autoscaling for the x-axis but then it
        // probably will start a little bit earlier than the first value
        // to make the first value an even number as it sees the timestamp
        // as an normal integer value.
        $graph->SetScale('intlin', 0, 200, $now, $datax[$n - 1]);

        // Setup the x-axis with a format callback to convert the timestamp
        // to a user readable time
        $graph->xaxis->SetLabelFormatCallback($TimeCallback);
        $graph->xaxis->SetLabelAngle(90);

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

        $_mapname = '__mapname' . rand(0, 1000000) . '__';

        $graph->Stroke();

        $game->setTemplateVar('MAP', $graph->GetHTMLImageMap($_mapname));
        $game->setTemplateVar('GRAPH', $graph->GetCSIMImgHTML($_mapname));
    }
}
