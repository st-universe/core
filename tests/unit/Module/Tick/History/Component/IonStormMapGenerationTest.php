<?php

declare(strict_types=1);

namespace Stu\Module\Tick\History\Component;

use GdImage;
use Imagick;
use Mockery\MockInterface;
use Stu\Module\Config\StuConfigInterface;
use Stu\Orm\Repository\AnomalyRepositoryInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\StuTestCase;

class IonStormMapGenerationTest extends StuTestCase
{
    private const WIDTH = 340;
    private const HEIGHT = 2;

    private MockInterface&AnomalyRepositoryInterface $anomalyRepository;
    private MockInterface&LayerRepositoryInterface $layerRepository;
    private MockInterface&StuConfigInterface $config;

    private IonStormMapGeneration $subject;

    private ?string $gifPath = null;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->anomalyRepository = $this->mock(AnomalyRepositoryInterface::class);
        $this->layerRepository = $this->mock(LayerRepositoryInterface::class);
        $this->config = $this->mock(StuConfigInterface::class);

        $this->subject = new IonStormMapGeneration(
            $this->anomalyRepository,
            $this->layerRepository,
            $this->config
        );
    }

    #[\Override]
    protected function tearDown(): void
    {
        if ($this->gifPath !== null && file_exists($this->gifPath)) {
            unlink($this->gifPath);
        }

        parent::tearDown();
    }

    public function testEditExistingGifRemovesOldestFrameBeforeAppendingNewFrame(): void
    {
        if (!extension_loaded('imagick')) {
            self::markTestSkipped('Imagick extension is required');
        }

        $this->gifPath = tempnam(sys_get_temp_dir(), 'ionstorm-map-');
        self::assertIsString($this->gifPath);

        $this->writeExistingGif($this->gifPath);

        $newFrame = $this->createGdFrame(169);

        $method = $this->getMethod($this->subject, 'editExistingGif');
        $method->invoke($this->subject, $this->gifPath, $newFrame);

        $frames = (new Imagick($this->gifPath))->coalesceImages();

        self::assertSame(168, $frames->getNumberImages());

        $frames->setFirstIterator();
        $firstFrame = $frames->getImage();

        self::assertFalse($this->isStormPixel($firstFrame, 1));
        self::assertTrue($this->isStormPixel($firstFrame, 2));

        $frames->setLastIterator();
        $lastFrame = $frames->getImage();

        self::assertFalse($this->isStormPixel($lastFrame, 168));
        self::assertTrue($this->isStormPixel($lastFrame, 169));
    }

    private function writeExistingGif(string $gifPath): void
    {
        $gif = new Imagick();
        $gif->setFormat('gif');

        for ($cx = 1; $cx <= 168; $cx++) {
            $gif->addImage($this->createImagickFrame($cx));
        }

        $gif = $gif->coalesceImages()->optimizeImageLayers();
        $gif->writeImages($gifPath, true);
    }

    private function createImagickFrame(int $cx): Imagick
    {
        $frame = new Imagick();
        $frame->readImageBlob($this->createImageBlob($this->createGdFrame($cx)));
        $frame->setImageDelay(25);

        return $frame;
    }

    private function createGdFrame(int $cx): GdImage
    {
        $img = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        if ($img === false) {
            self::fail('could not create gd image');
        }

        $black = imagecolorallocate($img, 0, 0, 0);
        if ($black === false) {
            self::fail('could not allocate black');
        }
        imagefill($img, 0, 0, $black);

        $lightBlue = imagecolorallocate($img, 173, 216, 230);
        if ($lightBlue === false) {
            self::fail('could not allocate light blue');
        }

        $x = ($cx - 1) * 2;
        imagefilledrectangle($img, $x, 0, $x + 1, 1, $lightBlue);

        return $img;
    }

    private function createImageBlob(GdImage $img): string
    {
        ob_start();
        imagegif($img);
        $buffer = ob_get_clean();
        if ($buffer === false) {
            self::fail('could not create image blob');
        }

        return $buffer;
    }

    private function isStormPixel(Imagick $frame, int $cx): bool
    {
        $color = $frame->getImagePixelColor(($cx - 1) * 2, 0)->getColor();

        return $color['r'] > 150
            && $color['g'] > 190
            && $color['b'] > 200;
    }
}
