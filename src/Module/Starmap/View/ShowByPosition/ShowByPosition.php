<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\View\ShowByPosition;

use Override;
use request;
use Stu\Component\Game\GameEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Starmap\Lib\StarmapUiFactoryInterface;
use Stu\Module\Starmap\View\RefreshSection\RefreshSection;

final class ShowByPosition implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_STARMAP_POSITION';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private StarmapUiFactoryInterface $starmapUiFactory
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $ship =  $this->spacecraftLoader->getByIdAndUser(
            request::getIntFatal('id'),
            $game->getUser()->getId(),
            true,
            false
        );

        $layer = $ship->getLayer();
        $section = $ship->getLocation()->getSectorId();

        if ($layer === null || $section === null) {
            throw new SanityCheckException('ship is in wormhole');
        }

        $game->setMacroInAjaxWindow('html/ship/starmap.twig');

        $helper = $this->starmapUiFactory->createMapSectionHelper();
        $helper->setTemplateVars(
            $game,
            $layer,
            $section
        );

        $game->addExecuteJS(sprintf(
            "registerNavKeys('%s.php', '%s', '%s');",
            ModuleEnum::STARMAP->value,
            RefreshSection::VIEW_IDENTIFIER,
            'html/map/starmapSectionTable.twig'
        ), GameEnum::JS_EXECUTION_AJAX_UPDATE);

        $game->addExecuteJS("updateNavigation();", GameEnum::JS_EXECUTION_AFTER_RENDER);
    }
}
