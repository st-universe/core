<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowAnalyseBuoy;

use Override;
use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\BuoyRepositoryInterface;

final class ShowAnalyseBuoy implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_ANALYSE_BUOY';


    public function __construct(private BuoyRepositoryInterface $buoyRepository) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $buoy = $this->buoyRepository->find(
            request::indInt('id')
        );

        $game->setPageTitle("Boje analysieren");
        $game->setMacroInAjaxWindow('html/ship/analysebuoy.twig');

        $amplitude = $buoy !== null ? $buoy->getId() * $buoy->getUserId() : 0;
        $wavelength = ceil($amplitude / 2);

        $game->setTemplateVar('AMPLITUDE', $amplitude);
        $game->setTemplateVar('WAVELENGTH', $wavelength);
        $game->setTemplateVar('BUOY', $buoy);
        $game->setTemplateVar('USER', $user);
    }
}
