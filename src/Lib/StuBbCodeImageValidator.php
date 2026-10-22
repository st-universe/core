<?php

declare(strict_types=1);

namespace Stu\Lib;

use JBBCode\InputValidator;

final class StuBbCodeImageValidator implements InputValidator
{
    /**
     * Returns true if $input is a valid url and correct image type
     *
     * @param string $input  the string to validate
     */
    #[\Override]
    public function validate($input): bool
    {
        $scheme = parse_url($input, PHP_URL_SCHEME);

        return filter_var($input, FILTER_VALIDATE_URL) !== false
            && is_string($scheme)
            && in_array(strtolower($scheme), ['http', 'https'], true);
    }
}
