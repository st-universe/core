<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnPlot;

use Stu\Component\Communication\Kn\KnFactoryInterface;
use Stu\Component\Communication\Kn\KnItemInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;

final class ShowKnPlot implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_PLOT';

    private $showKnPlotRequest;

    private $knPostRepository;

    private $rpgPlotRepository;

    private $knFactory;

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
        $game->setTemplateVar(
            'POSTS',
            array_map(
                function (KnPostInterface $knPost) use ($user): KnItemInterface {
                    return $this->knFactory->createKnItem(
                        $knPost,
                        $user
                    );
                },
                $this->knPostRepository->getByPlot($plot, null, null)
            )
        );
    }
}
