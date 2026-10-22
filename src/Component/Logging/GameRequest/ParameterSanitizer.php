<?php

declare(strict_types=1);

namespace Stu\Component\Logging\GameRequest;

use Stu\Orm\Entity\GameRequest;

/**
 * Cleans the parameter list from privacy/security related ones
 */
final class ParameterSanitizer implements ParameterSanitizerInterface
{
    /** @var array<string> */
    private const array PARAMETER_BLACKLIST = [
        '_',
        'email',
        'sstr',
        'login',
        'loginname',
        'mobile',
        'pass',
        'pass2',
        'oldpass',
        'password',
        'password2',
    ];

    #[\Override]
    public function sanitize(
        GameRequest $gameRequest
    ): GameRequest {
        $params = $this->sanitizeValue($gameRequest->getParameterArray());
        if (is_array($params)) {
            $gameRequest->setParameterArray($params);
        }

        return $gameRequest;
    }

    private function sanitizeValue(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }
        $result = [];
        foreach ($value as $key => $entry) {
            if (is_string($key) && $this->isSensitive($key)) {
                continue;
            }
            $result[$key] = $this->sanitizeValue($entry);
        }
        return $result;
    }

    private function isSensitive(string $key): bool
    {
        $normalized = strtolower(preg_replace('/[^a-z0-9]/i', '', $key) ?? '');
        if ($key === '_' || in_array($normalized, self::PARAMETER_BLACKLIST, true)) {
            return true;
        }
        foreach (['token', 'secret', 'pass', 'credential', 'auth', 'cookie', 'session', 'apikey', 'privatekey', 'sstr', 'csrf', 'xsrf', 'bearer', 'code', 'nonce', 'email', 'mobile', 'login'] as $sensitive) {
            if (str_contains($normalized, $sensitive)) {
                return true;
            }
        }
        return false;
    }

}
