<?php

declare(strict_types=1);

namespace Stu\Component\Logging\GameRequest;

use Override;
use Stu\Orm\Entity\GameRequest;

/**
 * Cleans the parameter list from privacy/security related ones
 */
final class ParameterSanitizer implements ParameterSanitizerInterface
{
    /** @var array<string> */
    private const array PARAMETER_BLACKLIST = [
        '_',
        'sstr',
        'login',
        'pass',
        'pass2',
        'oldpass',
    ];

    #[Override]
    public function sanitize(
        GameRequest $gameRequest
    ): GameRequest {
        $params = array_diff_key(
            $gameRequest->getParameterArray(),
            array_flip(self::PARAMETER_BLACKLIST)
        );

        $gameRequest->setParameterArray($params);

        return $gameRequest;
    }
}
