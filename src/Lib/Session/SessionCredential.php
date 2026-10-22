<?php

declare(strict_types=1);

namespace Stu\Lib\Session;

use Stu\Orm\Entity\User;

final class SessionCredential
{
    public static function bind(User $user): void
    {
        $_SESSION['credential_fingerprint'] = self::fingerprint($user);
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public static function isValid(User $user): bool
    {
        $fingerprint = $_SESSION['credential_fingerprint'] ?? null;

        return is_string($fingerprint) && hash_equals(self::fingerprint($user), $fingerprint);
    }

    private static function fingerprint(User $user): string
    {
        return hash('sha256', $user->getId() . ':' . $user->getRegistration()->getPassword());
    }
}
