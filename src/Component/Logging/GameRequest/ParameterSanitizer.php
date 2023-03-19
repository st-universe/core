<?php

declare(strict_types=1);

namespace Stu\Component\Logging\GameRequest;

use Stu\Orm\Entity\GameRequestInterface;

/**
 * Cleans the parameter list from privacy/security related ones
 */
final class ParameterSanitizer implements ParameterSanitizerInterface
{
    /** @var array<string> */
    private const PARAMETER_BLACKLIST = [
        '_',
        'sstr',
        'login',
        'pass',
        'pass2',
        'oldpass',
    ];

    public function sanitize(
        GameRequestInterface $gameRequest
    ): GameRequestInterface {
        $params = array_diff_key(
            $gameRequest->getParameterArray(),
            array_flip(self::PARAMETER_BLACKLIST)
        );

        $gameRequest->setParameterArray($params);

        return $gameRequest;
    }
}
