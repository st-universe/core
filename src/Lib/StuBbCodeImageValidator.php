<?php

declare(strict_types=1);

namespace Stu\Lib;

use Override;
use JBBCode\InputValidator;
use JBBCode\validators\UrlValidator;

final class StuBbCodeImageValidator implements InputValidator
{
    /**
     * Returns true if $input is a valid url and correct image type
     *
     * @param string $input  the string to validate
     */
    #[Override]
    public function validate($input): bool
    {
        $urlValidator = new UrlValidator();

        return $urlValidator->validate($input) && $this->isJpgOrPng($input);
    }

    private function isJpgOrPng(string $input): bool
    {
        $imageType = exif_imagetype($input);

        return $imageType === IMAGETYPE_JPEG || $imageType === IMAGETYPE_PNG;
    }
}
