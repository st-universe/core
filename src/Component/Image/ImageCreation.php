<?php

declare(strict_types=1);

namespace Stu\Component\Image;

use Amenadiel\JpGraph\Graph\Graph;
use GdImage;
use RuntimeException;

final class ImageCreation implements ImageCreationInterface
{
    #[\Override]
    public function graphInSrc(Graph $graph): string
    {
        $img = $graph->Stroke('__handle');

        return $this->gdImageInSrc($img);
    }

    #[\Override]
    public function gdImageInSrc(GdImage $gdImage, string $format = 'png'): string
    {
        ob_start();
        if ($format === 'png') {
            imagepng($gdImage);
        } else {
            imagegif($gdImage);
        }
        $img_data = ob_get_contents();

        if ($img_data === false) {
            throw new RuntimeException('Output buffering is not active');
        }
        ob_end_clean();

        return '<img src="data:image/' . $format . ';base64,' . base64_encode($img_data) . '"/>';
    }
}
