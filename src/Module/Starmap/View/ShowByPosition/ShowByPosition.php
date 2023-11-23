<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\View\ShowByPosition;

use request;
use Stu\Component\Game\GameEnum;
use Stu\Component\Game\ModuleViewEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Starmap\Lib\StarmapUiFactoryInterface;
use Stu\Module\Starmap\View\RefreshSection\RefreshSection;

final class ShowByPosition implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_STARMAP_POSITION';

    private ShipLoaderInterface $shipLoader;

    private StarmapUiFactoryInterface $starmapUiFactory;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        StarmapUiFactoryInterface $starmapUiFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->starmapUiFactory = $starmapUiFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $ship =  $this->shipLoader->getByIdAndUser(request::getIntFatal('sid'), $game->getUser()->getId(), true);

        $layer = $ship->getLayer();
        $section = $ship->getSectorId();

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
            "registerNavKeys('%s.php', '%s', '%s', false);",
            ModuleViewEnum::MAP->value,
            RefreshSection::VIEW_IDENTIFIER,
            'html/starmapSectionTable.twig'
        ), GameEnum::JS_EXECUTION_AJAX_UPDATE);

        $game->addExecuteJS("updateNavigation();", GameEnum::JS_EXECUTION_AFTER_RENDER);
    }
}
