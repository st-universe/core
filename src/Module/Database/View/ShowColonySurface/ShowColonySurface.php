<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\ShowColonySurface;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\ColonyScanRepositoryInterface;

final class ShowColonySurface implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SURFACE';

    public function __construct(private ColonyScanRepositoryInterface $colonyScanRepository, private ShowColonySurfaceRequestInterface $showColonySurfaceRequest)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setPageTitle(_('Letzter Oberflächenscan'));
        $game->setMacroInAjaxWindow('html/databasemacros.xhtml/colonysurface');


        $id = $this->showColonySurfaceRequest->getId();

        $game->setTemplateVar('SURFACE', unserialize($this->colonyScanRepository->getSurfaceArray($id)));
        $game->setTemplateVar('CSSCLASS', 'cfu');
        $game->setTemplateVar('SURFACETILESTYLE', $this->getSurfaceTileStyle());
    }

    public function getSurfaceTileStyle(): string
    {
        $width = $this->colonyScanRepository->getSurfaceWidth($this->showColonySurfaceRequest->getId());
        $gridArray = [];
        for ($i = 0; $i < $width; $i++) {
            $gridArray[] = '43px';
        }
        return sprintf('display: grid; grid-template-columns: %s;', implode(' ', $gridArray));
    }
}
