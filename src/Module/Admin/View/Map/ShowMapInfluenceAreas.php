<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Map;

use Override;
use request;
use RuntimeException;
use Stu\Component\Image\ImageCreationInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

final class ShowMapInfluenceAreas implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_INFLUENCE_AREAS';

    public function __construct(private MapRepositoryInterface $mapRepository, private LayerRepositoryInterface $layerRepository, private ImageCreationInterface $imageCreation)
    {
    }

    #[Override]
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
        $game->setTemplateFile('html/admin/influenceareas.twig');

        $game->setTemplateVar('GRAPH', $this->imageCreation->gdImageInSrc($this->buildImage($layer, $showAllyAreas !== 0)));
    }

    private function buildImage(LayerInterface $layer, bool $showAllyAreas): mixed
    {
        $img = imagecreatetruecolor($layer->getWidth() * 15, $layer->getHeight() * 15);

        // mapfields
        $startY = 1;
        $cury = 0;
        $curx = 0;

        foreach ($this->mapRepository->getAllOrdered($layer->getId()) as $data) {
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
                            if (
                                !is_int($red) || $red < 1 || $red > 255
                                || !is_int($green) || $green < 1 || $green > 255
                                || !is_int($blue) || $blue < 1 || $blue > 255
                            ) {
                                throw new RuntimeException('rgb range exception');
                            }
                            $col = imagecolorallocate($border, $red, $green, $blue);
                        }
                    }
                }
            }

            if ($col === null) {
                $rest = $id % 200;
                if ($rest < 1) {
                    throw new RuntimeException('rgb range exception');
                }
                $col = imagecolorallocate($border, $rest, $rest, $rest);
            }

            if (!$col) {
                throw new RuntimeException('color range exception');
            }
            imagefill($border, 0, 0, $col);
            imagecopy($img, $border, $curx, $cury, 0, 0, 15, 15);
            $curx += 15;
        }

        return $img;
    }
}
