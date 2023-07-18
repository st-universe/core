<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnPlot;

use Stu\Component\Communication\Kn\KnFactoryInterface;
use Stu\Component\Communication\Kn\KnItemInterface;
use Stu\Component\Game\GameEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;

final class ShowKnPlot implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_PLOT';

    private ShowKnPlotRequestInterface $showKnPlotRequest;

    private KnPostRepositoryInterface $knPostRepository;

    private RpgPlotRepositoryInterface $rpgPlotRepository;

    private KnFactoryInterface $knFactory;

    public function __construct(
        ShowKnPlotRequestInterface $showKnPlotRequest,
        KnPostRepositoryInterface $knPostRepository,
        RpgPlotRepositoryInterface $rpgPlotRepository,
        KnFactoryInterface $knFactory
    ) {
        $this->showKnPlotRequest = $showKnPlotRequest;
        $this->knPostRepository = $knPostRepository;
        $this->rpgPlotRepository = $rpgPlotRepository;
        $this->knFactory = $knFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $plot = $this->rpgPlotRepository->find($this->showKnPlotRequest->getPlotId());

        if ($plot === null) {
            return;
        }
        $mark = $this->showKnPlotRequest->getKnOffset();

        if ($mark % GameEnum::KN_PER_SITE != 0 || $mark < 0) {
            $mark = 0;
        }
        $maxcount = $this->knPostRepository->getAmountByPlot((int) $plot->getId());
        $maxpage = ceil($maxcount / GameEnum::KN_PER_SITE);
        $curpage = floor($mark / GameEnum::KN_PER_SITE);
        $knNavigation = [];
        if ($curpage != 0) {
            $knNavigation[] = ["page" => "<<", "mark" => 0, "cssclass" => "pages"];
            $knNavigation[] = ["page" => "<", "mark" => ($mark - GameEnum::KN_PER_SITE), "cssclass" => "pages"];
        }
        for ($i = $curpage - 1; $i <= $curpage + 3; $i++) {
            if ($i > $maxpage || $i < 1) {
                continue;
            }
            $knNavigation[] = [
                "page" => $i,
                "mark" => ($i * GameEnum::KN_PER_SITE - GameEnum::KN_PER_SITE),
                "cssclass" => ($curpage + 1 === $i ? "pages selected" : "pages")
            ];
        }
        if ($curpage + 1 !== $maxpage) {
            $knNavigation[] = ["page" => ">", "mark" => ($mark + GameEnum::KN_PER_SITE), "cssclass" => "pages"];
            $knNavigation[] = ["page" => ">>", "mark" => $maxpage * GameEnum::KN_PER_SITE - GameEnum::KN_PER_SITE, "cssclass" => "pages"];
        }

        $game->setTemplateFile('html/plotdetails.xhtml');
        $game->setPageTitle(sprintf('Plot: %s', $plot->getTitle()));

        $game->appendNavigationPart('comm.php', _('KommNet'));
        $game->appendNavigationPart('comm.php?SHOW_PLOTLIST=1', _('Plots'));
        $game->appendNavigationPart(
            sprintf(
                'comm.php?%s=1&plotid=%d',
                static::VIEW_IDENTIFIER,
                $plot->getId()
            ),
            $plot->getTitle()
        );

        $game->setTemplateVar(
            'KN_POSTINGS',
            array_map(
                fn(KnPostInterface $knPost): KnItemInterface => $this->knFactory->createKnItem(
                    $knPost,
                    $user
                ),
                $this->knPostRepository->getByPlot($plot, $mark, GameEnum::KN_PER_SITE)
            )
        );
        $game->setTemplateVar('KN_OFFSET', $mark);
        $game->setTemplateVar('KN_NAVIGATION', $knNavigation);
        $game->setTemplateVar('PLOT', $plot);
        $game->setTemplateVar('MAY_EDIT', $plot->getUserId() === $game->getUser()->getId());
        $game->setTemplateVar(
            'POSTS',
            array_map(
                fn(KnPostInterface $knPost): KnItemInterface => $this->knFactory->createKnItem(
                    $knPost,
                    $user
                ),
                $this->knPostRepository->getByPlot($plot, null, null)
            )
        );
    }
}
