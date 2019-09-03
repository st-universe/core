<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnPlot;

use RPGPlot;
use Stu\Module\Communication\Lib\KnTalFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;

final class ShowKnPlot implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_PLOT';

    private $showKnPlotRequest;

    private $knPostRepository;

    private $knTalFactory;

    public function __construct(
        ShowKnPlotRequestInterface $showKnPlotRequest,
        KnPostRepositoryInterface $knPostRepository,
        KnTalFactoryInterface $knTalFactory
    ) {
        $this->showKnPlotRequest = $showKnPlotRequest;
        $this->knPostRepository = $knPostRepository;
        $this->knTalFactory = $knTalFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $plot = new RPGPlot($this->showKnPlotRequest->getPlotId());

        $list = [];

        foreach ($this->knPostRepository->getByPlot((int) $plot->getId(), null, null) as $post) {
            $list[] = $this->knTalFactory->createKnPostTal(
                $post,
                $user
            );
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

        $game->setTemplateVar('PLOT', $plot);
        $game->setTemplateVar('MAY_EDIT', $plot->getUserId() == $game->getUser()->getId());
        $game->setTemplateVar('POSTS', $list);
    }
}
