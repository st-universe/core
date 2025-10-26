<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnPlot;

use Stu\Component\Communication\Kn\KnFactoryInterface;
use Stu\Component\Communication\Kn\KnItemInterface;
use Stu\Component\Game\GameEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\KnPost;
use Stu\Orm\Repository\KnPostRepositoryInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;

final class ShowKnPlot implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_PLOT';

    public function __construct(private ShowKnPlotRequestInterface $showKnPlotRequest, private KnPostRepositoryInterface $knPostRepository, private RpgPlotRepositoryInterface $rpgPlotRepository, private KnFactoryInterface $knFactory) {}

    #[\Override]
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
        $maxcount = $this->knPostRepository->getAmountByPlot($plot->getId());
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

        $game->setViewTemplate('html/communication/plotdetails.twig');
        $game->setPageTitle(sprintf('Plot: %s', $plot->getTitle()));

        $game->appendNavigationPart('comm.php', _('KommNet'));
        $game->appendNavigationPart('comm.php?SHOW_PLOTLIST=1', _('Plots'));
        $game->appendNavigationPart(
            sprintf(
                'comm.php?%s=1&plotid=%d',
                self::VIEW_IDENTIFIER,
                $plot->getId()
            ),
            $plot->getTitle()
        );

        $game->setTemplateVar(
            'KN_POSTINGS',
            array_map(
                fn(KnPost $knPost): KnItemInterface => $this->knFactory->createKnItem(
                    $knPost,
                    $user
                ),
                $this->knPostRepository->getByPlot($plot, $mark, GameEnum::KN_PER_SITE)
            )
        );
        $game->setTemplateVar('USER', $game->isAdmin());
        $game->setTemplateVar('KN_OFFSET', $mark);
        $game->setTemplateVar('KN_NAVIGATION', $knNavigation);
        $game->setTemplateVar('PLOT', $plot);
        $game->setTemplateVar('MAY_EDIT', $plot->getUserId() === $game->getUser()->getId());
        $game->setTemplateVar(
            'POSTS',
            array_map(
                fn(KnPost $knPost): KnItemInterface => $this->knFactory->createKnItem(
                    $knPost,
                    $user
                ),
                $this->knPostRepository->getByPlot($plot, null, null)
            )
        );
    }
}
