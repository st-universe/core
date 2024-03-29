<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowBuoyList;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\BuoyRepositoryInterface;

final class ShowBuoyList implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_BUOY_LIST';

    private BuoyRepositoryInterface $buoyRepository;

    public function __construct(
        BuoyRepositoryInterface $buoyRepository,
    ) {
        $this->buoyRepository = $buoyRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $buoy = $this->buoyRepository->findByUserId($userId);

        $game->setTemplateVar('BUOY', $buoy);
        $game->showMacro('html/buoylist.twig');
    }
}