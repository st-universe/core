<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnPlot;

use Stu\Component\Communication\Kn\KnFactoryInterface;
use Stu\Component\Communication\Kn\KnItemInterface;
use Stu\Component\Game\GameEnum;
use Stu\Lib\Paging\PagingFactory;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\GameUserRoleCheckerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\KnPost;
use Stu\Orm\Repository\KnPostRepositoryInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;

final class ShowKnPlot implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_PLOT';

    public function __construct(
        private readonly ShowKnPlotRequestInterface $showKnPlotRequest,
        private readonly KnPostRepositoryInterface $knPostRepository,
        private readonly RpgPlotRepositoryInterface $rpgPlotRepository,
        private readonly KnFactoryInterface $knFactory,
        private readonly PagingFactory $pagingFactory,
        private readonly GameUserRoleCheckerInterface $gameUserRoleChecker
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $plot = $this->rpgPlotRepository->find($this->showKnPlotRequest->getPlotId());

        if ($plot === null) {
            return;
        }
        $mark = $this->showKnPlotRequest->getKnOffset();

        if ($mark % GameEnum::KN_PER_SITE !== 0 || $mark < 0) {
            $mark = 0;
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
                fn (KnPost $knPost): KnItemInterface => $this->knFactory->createKnItem(
                    $knPost,
                    $user
                ),
                $this->knPostRepository->getByPlot($plot, $mark, GameEnum::KN_PER_SITE)
            )
        );
        $game->setTemplateVar('USER', $this->gameUserRoleChecker->isAdmin());
        $game->setTemplateVar('KN_OFFSET', $mark);
        $game->setTemplateVar('PLOT', $plot);
        $game->setTemplateVar('MAY_EDIT', $plot->getUserId() === $game->getUser()->getId());
        $game->setTemplateVar(
            'POSTS',
            array_map(
                fn (KnPost $knPost): KnItemInterface => $this->knFactory->createKnItem(
                    $knPost,
                    $user
                ),
                $this->knPostRepository->getByPlot($plot, null, null)
            )
        );
        $game->setTemplateVar('PAGING', $this->pagingFactory->createPaging(
            $this->knPostRepository->getAmountByPlot($plot->getId()),
            GameEnum::KN_PER_SITE,
            $mark,
            sprintf('?%s=1&plotid=%d', self::VIEW_IDENTIFIER, $plot->getId())
        ));
    }
}
