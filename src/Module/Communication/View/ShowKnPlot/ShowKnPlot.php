<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnPlot;

use Stu\Module\Communication\Lib\KnTalFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\RpgPlotInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;

final class ShowKnPlot implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_PLOT';

    private $showKnPlotRequest;

    private $knPostRepository;

    private $knTalFactory;

    private $rpgPlotRepository;

    public function __construct(
        ShowKnPlotRequestInterface $showKnPlotRequest,
        KnPostRepositoryInterface $knPostRepository,
        KnTalFactoryInterface $knTalFactory,
        RpgPlotRepositoryInterface $rpgPlotRepository
    ) {
        $this->showKnPlotRequest = $showKnPlotRequest;
        $this->knPostRepository = $knPostRepository;
        $this->knTalFactory = $knTalFactory;
        $this->rpgPlotRepository = $rpgPlotRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        /** @var RpgPlotInterface $plot */
        $plot = $this->rpgPlotRepository->find($this->showKnPlotRequest->getPlotId());

        if ($plot === null) {
            return;
        }

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
