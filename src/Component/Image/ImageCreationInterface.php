<?php

declare(strict_types=1);

namespace Stu\Component\Image;

use Amenadiel\JpGraph\Graph\Graph;

interface ImageCreationInterface
{
    public function graphInSrc(Graph $graph): string;

    public function gdImageInSrc($gdImage): string;
}
