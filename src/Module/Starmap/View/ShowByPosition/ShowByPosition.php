<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\View\ShowByPosition;

use Stu\Component\Game\ModuleViewEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Starmap\Lib\MapSectionHelper;
use Stu\Module\Starmap\View\ShowSection\ShowSectionRequestInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;

final class ShowByPosition implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_STARMAP_POSITION';

    private ShowSectionRequestInterface $request;

    private LayerRepositoryInterface $layerRepository;

    public function __construct(
        ShowSectionRequestInterface $request,
        LayerRepositoryInterface $layerRepository
    ) {
        $this->request = $request;
        $this->layerRepository = $layerRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $layerId = $this->request->getLayerId();
        $layer = $this->layerRepository->find($layerId);

        $xCoordinate = $this->request->getXCoordinate($layer);
        $yCoordinate = $this->request->getYCoordinate($layer);
        $sectionId = $this->request->getSectionId();

        //sanity check if user knows layer
        if (!$game->getUser()->hasSeen($layer->getId())) {
            throw new SanityCheckException('user tried to access unseen layer');
        }

        $game->setMacroInAjaxWindow('html/macros.xhtml/starmap');

        $helper = new MapSectionHelper();
        $helper->setTemplateVars(
            $game,
            $layer,
            $xCoordinate,
            $yCoordinate,
            $sectionId,
            ModuleViewEnum::MODULE_VIEW_STARMAP,
            self::VIEW_IDENTIFIER
        );
    }
}
