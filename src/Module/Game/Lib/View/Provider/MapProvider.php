<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View\Provider;

use request;
use Stu\Component\Map\MapEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Game\Lib\View\Provider\ViewComponentProviderInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;

final class MapProvider implements ViewComponentProviderInterface
{
    private LayerRepositoryInterface $layerRepository;

    public function __construct(LayerRepositoryInterface $layerRepository)
    {
        $this->layerRepository = $layerRepository;
    }

    public function setTemplateVariables(GameControllerInterface $game): void
    {
        //only layers, that are known by user
        $layers = $this->layerRepository->getKnownByUser($game->getUser()->getId());

        if ($layers === []) {
            return;
        }

        $layerId = request::getInt('layerid');
        if ($layerId === 0) {
            $layer = current($layers);
        } else {
            if (!array_key_exists($layerId, $layers)) {
                throw new SanityCheckException('user tried to access unknown layer');
            }

            $layer = $layers[$layerId];
        }
        $game->setTemplateVar('LAYERID', $layer->getId());

        //HEADROW
        $xHeadRow = [];
        for ($j = 1; $j <= (int)ceil($layer->getWidth() / MapEnum::FIELDS_PER_SECTION); $j++) {
            $xHeadRow[] = $j;
        }

        //SECTIONS
        $sections = [];
        $k = 1;
        for ($i = 1; $i <= (int)ceil($layer->getHeight() / MapEnum::FIELDS_PER_SECTION); $i++) {
            for ($j = 1; $j <= (int)ceil($layer->getWidth() / MapEnum::FIELDS_PER_SECTION); $j++) {
                $sections[$i][$j] = $k;
                $k++;
            }
        }

        $game->setTemplateVar('LAYERS', $layers);
        $game->setTemplateVar('X_HEAD_ROW', $xHeadRow);
        $game->setTemplateVar('SECTIONS', $sections);
        $game->setTemplateVar('FIELDS_PER_SECTION', MapEnum::FIELDS_PER_SECTION);
    }
}
