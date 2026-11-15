<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\View\ShowByPosition;

use Override;
use request;
use Stu\Component\Game\JavascriptExecutionTypeEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Exception\AccessViolationException;
use Stu\Exception\SpacecraftDoesNotExistException;
use Stu\Exception\UnallowedUplinkOperationException;
use Stu\Module\Control\Component\View\ViewControllerContext;
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
    public function handle(ViewControllerContext $game): void
    {
        $game->setMacroInAjaxWindow('');
        try {
            $ship = $this->spacecraftLoader->getByIdAndUser(
                request::getIntFatal('id'),
                $game->getUser()->getId(),
                true,
                false
            );
        } catch (AccessViolationException | SpacecraftDoesNotExistException | UnallowedUplinkOperationException) {
            $game->setPageTitle('Sternenkartenposition');
            $game->getInfo()->addInformation('Die Schiffsposition ist nicht verfügbar');
            return;
        }

        $layer = $ship->getLayer();
        $section = $ship->getLocation()->getSectorId();

        if ($layer === null || $section === null) {
            $game->setPageTitle('Sternenkartenposition');
            $game->getInfo()->addInformation('An dieser Position ist keine Sternenkarte verfügbar');
            return;
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
        ), JavascriptExecutionTypeEnum::ON_AJAX_UPDATE);

        $game->addExecuteJS("updateNavigation();", JavascriptExecutionTypeEnum::AFTER_RENDER);
    }
}
