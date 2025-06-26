<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Map;

use Override;
use request;
use RuntimeException;
use Stu\Component\Image\ImageCreationInterface;
use Stu\Component\Player\Settings\UserSettingsProviderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

final class ShowMapInfluenceAreas implements ViewControllerInterface
{
	public const string VIEW_IDENTIFIER = 'SHOW_INFLUENCE_AREAS';

	public function __construct(
		private readonly MapRepositoryInterface $mapRepository,
		private readonly LayerRepositoryInterface $layerRepository,
		private readonly ImageCreationInterface $imageCreation,
		private readonly UserSettingsProviderInterface $userSettingsProvider
	) {}

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
				self::VIEW_IDENTIFIER
			),
			_('Einflussgebiete')
		);
		$game->setTemplateFile('html/admin/influenceareas.twig');

		$game->setTemplateVar('GRAPH', $this->imageCreation->gdImageInSrc($this->buildImage($layer, $showAllyAreas !== 0)));
	}

	private function buildImage(Layer $layer, bool $showAllyAreas): mixed
	{
		$width = $layer->getWidth() * 15;
		$height = $layer->getHeight() * 15;

		if ($width < 1 || $height < 1) {
			throw new RuntimeException('Ungültige Dimensionen für die Bilderstellung');
		}

		$img = imagecreatetruecolor($width, $height);

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
					$base = $influenceArea->getStation();

					if ($base !== null) {
						$ally = $base->getUser()->getAlliance();

						$rgbCode = ($ally !== null && $ally->getRgbCode() !== '')
							? $ally->getRgbCode()
							: $this->userSettingsProvider->getRgbCode($base->getUser());


						if ($rgbCode !== '') {
							$red = 100;
							$green = 100;
							$blue = 100;

							$ret = [];
							if (mb_eregi("[#]?([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})", $rgbCode, $ret)) {
								$red = (int) hexdec($ret[1]);
								$green = (int) hexdec($ret[2]);
								$blue = (int) hexdec($ret[3]);
							}

							$red = $this->validateRgb($red);
							$green = $this->validateRgb($green);
							$blue = $this->validateRgb($blue);

							$col = imagecolorallocate($border, $red, $green, $blue);
						}
					}
				}
			}

			if ($col === null) {
				$rest = $id % 200;
				$rest = max(1, $rest);
				$rest = $this->validateRgb($rest);
				$col = imagecolorallocate($border, $rest, $rest, $rest);
			}

			if ($col === false) {
				throw new RuntimeException('color range exception');
			}
			imagefill($border, 0, 0, $col);
			imagecopy($img, $border, $curx, $cury, 0, 0, 15, 15);
			$curx += 15;
		}

		return $img;
	}

	/** @return int<0, 255> */
	private function validateRgb(int $value): int
	{
		return max(0, min(255, $value));
	}
}
