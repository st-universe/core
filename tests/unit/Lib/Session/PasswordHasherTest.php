<?php

declare(strict_types=1);

namespace Stu\Lib\Session;

use PHPUnit\Framework\TestCase;

final class PasswordHasherTest extends TestCase
{
    public function testProductionHashUsesNativeDefaults(): void
    {
        $hash = new PasswordHasher()->hash('Default123');

        self::assertSame(PASSWORD_DEFAULT, password_get_info($hash)['algo']);
        self::assertFalse(password_needs_rehash($hash, PASSWORD_DEFAULT));
    }

    public function testHashesRemainSaltedAndVerifyOnlyTheCorrectPassword(): void
    {
        $hasher = new PasswordHasher(4);
        $hash = $hasher->hash('Original123');

        self::assertNotSame($hash, $hasher->hash('Original123'));
        self::assertTrue($hasher->verify('Original123', $hash));
        self::assertFalse($hasher->verify('Wrong123', $hash));
        self::assertFalse($hasher->verify('Original123', 'invalid-hash'));
    }
}
