<?php

declare(strict_types=1);

namespace Stu\Component\Image;

use Amenadiel\JpGraph\Graph\Graph;
use GdImage;

interface ImageCreationInterface
{
    public function graphInSrc(Graph $graph): string;

    /**
     * @param 'png'|'gif' $format
     */
    public function gdImageInSrc(GdImage $gdImage, string $format = 'png'): string;
}
