<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowAnalyseBuoy;

use request;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

final class ShowAnalyseBuoy implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_ANALYSE_BUOY';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId(),
            true,
            false
        );

        $buoy = $wrapper->get()->getLocation()->getBuoys()->get(request::indInt('buoyid'));
        if ($buoy === null) {
            throw new SanityCheckException('buoy on different location', null, self::VIEW_IDENTIFIER);
        }

        $game->setPageTitle("Boje analysieren");
        $game->setMacroInAjaxWindow('html/ship/analysebuoy.twig');

        $amplitude = $buoy->getId() * $buoy->getUserId();
        $wavelength = ceil($amplitude / 2);

        $game->setTemplateVar('AMPLITUDE', $amplitude);
        $game->setTemplateVar('WAVELENGTH', $wavelength);
        $game->setTemplateVar('BUOY', $buoy);
        $game->setTemplateVar('USER', $user);
    }
}
