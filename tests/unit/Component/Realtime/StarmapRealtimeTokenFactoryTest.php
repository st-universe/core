<?php

declare(strict_types=1);

namespace Stu\Component\Realtime;

use InvalidArgumentException;
use Noodlehaus\Config;
use Stu\StuTestCase;

final class StarmapRealtimeTokenFactoryTest extends StuTestCase
{
    private string|false $previousSecret;

    protected function setUp(): void
    {
        parent::setUp();
        $this->previousSecret = getenv('STU_REALTIME_SECRET');
        putenv('STU_REALTIME_SECRET');
    }

    protected function tearDown(): void
    {
        putenv($this->previousSecret === false ? 'STU_REALTIME_SECRET' : 'STU_REALTIME_SECRET=' . $this->previousSecret);
        parent::tearDown();
    }

    public function testMissingOrInvalidSecretCannotIssueTokens(): void
    {
        foreach ([null, '', 'the key to encrypt map data', 'stu', 123, str_repeat('a', 31), str_repeat(' ', 64)] as $secret) {
            $config = new Config([]);
            $config->set('game.map.encryptionKey', $secret);
            try {
                new StarmapRealtimeTokenFactory($config)->create(101, 2);
                self::fail('Invalid signing secret was accepted');
            } catch (InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function testDedicatedSecretSignsTheIssuedClaims(): void
    {
        $secret = str_repeat('1234567890abcdef', 4);
        $config = new Config([]);
        $config->set('game.map.encryptionKey', $secret);
        $token = new StarmapRealtimeTokenFactory($config)->create(101, 2);
        [$payload, $signature] = explode('.', $token);
        self::assertSame(rtrim(strtr(base64_encode(hash_hmac('sha256', $payload, $secret, true)), '+/', '-_'), '='), $signature);
        $claims = json_decode(base64_decode(strtr($payload, '-_', '+/')), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(101, $claims['userId']);
        self::assertSame(2, $claims['layerId']);
        self::assertGreaterThan(time(), $claims['exp']);
    }

    public function testEnvironmentSecretHasPrecedence(): void
    {
        $secret = str_repeat('fedcba0987654321', 4);
        putenv('STU_REALTIME_SECRET=' . $secret);
        $config = new Config([]);
        $config->set('game.map.encryptionKey', str_repeat('a', 64));
        $token = new StarmapRealtimeTokenFactory($config)->create(101, 2);
        [$payload, $signature] = explode('.', $token);
        self::assertSame(rtrim(strtr(base64_encode(hash_hmac('sha256', $payload, $secret, true)), '+/', '-_'), '='), $signature);
    }

    public function testEmptyEnvironmentCannotFallBackToConfiguredSecret(): void
    {
        putenv('STU_REALTIME_SECRET=');
        $config = new Config([]);
        $config->set('game.map.encryptionKey', str_repeat('a', 64));
        $this->expectException(InvalidArgumentException::class);
        new StarmapRealtimeTokenFactory($config)->create(101, 2);
    }

    public function testDatabaseKeyCannotBeReused(): void
    {
        $secret = str_repeat('a', 64);
        $config = new Config([]);
        $config->set('game.map.encryptionKey', $secret);
        $config->set('db.database', $secret);
        $this->expectException(InvalidArgumentException::class);
        new StarmapRealtimeTokenFactory($config)->create(101, 2);
    }
}
