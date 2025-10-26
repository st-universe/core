<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\View\RefreshSection;

use request;
use RuntimeException;
use Stu\Component\Map\DirectionEnum;
use Stu\Exception\SanityCheckException;
use Stu\Lib\Trait\LayerExplorationTrait;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Starmap\Lib\StarmapUiFactoryInterface;
use Stu\Module\Starmap\View\ShowSection\ShowSectionRequestInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;

final class RefreshSection implements ViewControllerInterface
{
    use LayerExplorationTrait;

    public const string VIEW_IDENTIFIER = 'REFRESH_SECTION';

    public function __construct(
        private ShowSectionRequestInterface $request,
        private StarmapUiFactoryInterface $starmapUiFactory,
        private LayerRepositoryInterface $layerRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $layerId = $this->request->getLayerId();
        $layer = $this->layerRepository->find($layerId);
        if ($layer === null) {
            throw new RuntimeException(sprintf('layer with following id does not exist: %d', $layerId));
        }

        $section = $this->request->getSection();

        //sanity check if user knows layer
        if (!$this->hasSeen($game->getUser(), $layer)) {
            throw new SanityCheckException('user tried to access unseen layer');
        }

        $game->showMacro(request::getStringFatal('macro'));

        $helper = $this->starmapUiFactory->createMapSectionHelper();
        $directionValue = $this->request->getDirection();
        $helper->setTemplateVars(
            $game,
            $layer,
            $section,
            false,
            $directionValue !== null ? DirectionEnum::from($directionValue) : null
        );
    }
}
