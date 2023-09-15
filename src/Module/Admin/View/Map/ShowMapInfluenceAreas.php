<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Map;

use request;
use Stu\Component\Image\ImageCreationInterface;
use Stu\Component\Map\MapEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

final class ShowMapInfluenceAreas implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_INFLUENCE_AREAS';

    private MapRepositoryInterface $mapRepository;

    private LayerRepositoryInterface $layerRepository;

    private ImageCreationInterface $imageCreation;

    public function __construct(
        MapRepositoryInterface $mapRepository,
        LayerRepositoryInterface $layerRepository,
        ImageCreationInterface $imageCreation
    ) {
        $this->mapRepository = $mapRepository;
        $this->layerRepository = $layerRepository;
        $this->imageCreation = $imageCreation;
    }

    public function handle(GameControllerInterface $game): void
    {
        $showAllyAreas = request::getInt('showAlly');
        $layerId = request::getIntFatal('layerid');

        $layer = $this->layerRepository->find($layerId);
        if ($layer === null) {
            $game->addInformation(sprintf('layerId %d does not exist', $layerId));
            return;
        }

        $game->appendNavigationPart(
            sprintf(
                '/admin/?%s=1',
                static::VIEW_IDENTIFIER
            ),
            _('Einflussgebiete')
        );
        $game->setTemplateFile('html/admin/influenceareas.xhtml');

        $game->setTemplateVar('GRAPH', $this->imageCreation->gdImageInSrc($this->buildImage($layer, $showAllyAreas !== 0)));
    }

    private function buildImage(LayerInterface $layer, bool $showAllyAreas): mixed
    {
        $img = imagecreatetruecolor($layer->getWidth() * 15, $layer->getHeight() * 15);

        // mapfields
        $startY = 1;
        $cury = 0;
        $curx = 0;

        foreach ($this->mapRepository->getAllOrdered(MapEnum::LAYER_ID_TULLAMORE) as $data) {
            $col = null;

            if ($startY !== $data->getCy()) {
                $startY = $data->getCy();
                $curx = 0;
                $cury += 15;
            }

            $id = $data->getInfluenceAreaId();

            $border = imagecreatetruecolor(15, 15);
            if ($data->getSystem() !== null) {
                $col = imagecolorallocate($border, 255, 0, 0);
            } elseif ($showAllyAreas) {
                $influenceArea = $data->getInfluenceArea();
                if ($influenceArea !== null) {
                    $base = $influenceArea->getBase();

                    if ($base !== null) {
                        $ally = $base->getUser()->getAlliance();

                        $rgbCode = $ally !== null ? $ally->getRgbCode() : $base->getUser()->getRgbCode();

                        if ($rgbCode !== '') {
                            $red = 100;
                            $green = 100;
                            $blue = 100;

                            $ret = [];
                            if (mb_eregi("[#]?([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})", $rgbCode, $ret)) {
                                $red = hexdec($ret[1]);

                                $green = hexdec($ret[2]);

                                $blue = hexdec($ret[3]);
                            }
                            $col = imagecolorallocate($border, $red, $green, $blue);
                        }
                    }
                }
            }

            if ($col === null) {
                $rest = $id % 200;
                $col = imagecolorallocate($border, $rest, $rest, $rest);
            }

            imagefill($border, 0, 0, $col);
            imagecopy($img, $border, $curx, $cury, 0, 0, 15, 15);
            $curx += 15;
        }

        return $img;
    }
}
