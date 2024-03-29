<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowAnalyseBuoy;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\BuoyRepositoryInterface;

final class ShowAnalyseBuoy implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_ANALYSE_BUOY';

    private BuoyRepositoryInterface $buoyRepository;


    public function __construct(
        BuoyRepositoryInterface $buoyRepository
    ) {
        $this->buoyRepository = $buoyRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $buoy = $this->buoyRepository->find(
            request::indInt('id')
        );


        $game->setPageTitle("Boje analysieren");
        $game->setMacroInAjaxWindow('html/shipmacros.xhtml/analysebuoy');

        $game->setTemplateVar('ERROR', true);

        if ($buoy !== null) {
            $amplitude = $buoy->getId() * $buoy->getUserId();
        } else {
            $amplitude = 0;
        }
        $wavelength = ceil($amplitude / 2);

        $game->setTemplateVar('AMPLITUDE', $amplitude);
        $game->setTemplateVar('WAVELENGTH', $wavelength);



        $game->setTemplateVar('BUOY', $buoy);
        $game->setTemplateVar('USER', $user);
    }
}
