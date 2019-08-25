<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowPlotKn;

use KNPosting;
use PMCategory;
use RPGPlot;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Module\Communication\View\ShowKnPlot\ShowKnPlot;

final class ShowPlotKn implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_PLOTKN';

    private const KNLIMITER = 6;

    private $showPlotKnRequest;

    public function __construct(
        ShowPlotKnRequestInterface $showPlotKnRequest
    ) {
        $this->showPlotKnRequest = $showPlotKnRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $plot = new RPGPlot($this->showPlotKnRequest->getPlotId());
        $mark = $this->showPlotKnRequest->getKnOffset();

        if ($mark % static::KNLIMITER != 0 || $mark < 0) {
            $mark = 0;
        }
        $maxcount = KNPosting::countInstances(sprintf('plot_id = %d', $plot->getId()));
        $maxpage = ceil($maxcount / static::KNLIMITER);
        $curpage = floor($mark / static::KNLIMITER);
        $knNavigation = [];
        if ($curpage != 0) {
            $knNavigation[] = ["page" => "<<", "mark" => 0, "cssclass" => "pages"];
            $knNavigation[] = ["page" => "<", "mark" => ($mark - static::KNLIMITER), "cssclass" => "pages"];
        }
        for ($i = $curpage - 1; $i <= $curpage + 3; $i++) {
            if ($i > $maxpage || $i < 1) {
                continue;
            }
            $knNavigation[] = [
                "page" => $i,
                "mark" => ($i * static::KNLIMITER - static::KNLIMITER),
                "cssclass" => ($curpage + 1 == $i ? "pages selected" : "pages")
            ];
        }
        if ($curpage + 1 != $maxpage) {
            $knNavigation[] = ["page" => ">", "mark" => ($mark + static::KNLIMITER), "cssclass" => "pages"];
            $knNavigation[] = ["page" => ">>", "mark" => $maxpage * static::KNLIMITER - static::KNLIMITER, "cssclass" => "pages"];
        }

        $game->setTemplateFile('html/plotkn.xhtml');
        $game->appendNavigationPart('comm.php', _('KommNet'));
        $game->appendNavigationPart('comm.php?SHOW_PLOTLIST=1', _('Plots'));
        $game->appendNavigationPart(
            sprintf('comm.php?%s=1&plotid=%s', ShowKnPlot::VIEW_IDENTIFIER, $plot->getId()),
            $plot->getTitleDecoded()
        );
        $game->appendNavigationPart(
            sprintf('comm.php?%s=1&plotid=%s', static::VIEW_IDENTIFIER, $plot->getId()),
            _('Beiträge')
        );
        $game->setPageTitle("Plot: " . $plot->getTitleDecoded());

        $game->setTemplateVar(
            'KN_POSTINGS',
            KNPosting::getBy(
                sprintf(
                    'WHERE plot_id = %d ORDER BY date DESC LIMIT %d,%d',
                    $plot->getId(),
                    $mark,
                    static::KNLIMITER
                )
            )
        );
        $game->setTemplateVar('PLOT', $plot);
        $game->setTemplateVar('KN_OFFSET', $mark);
        $game->setTemplateVar('PM_CATEGORIES', PMCategory::getCategoryTree($game->getUser()->getId()));
        $game->setTemplateVar('KN_NAVIGATION', $knNavigation);
    }
}
