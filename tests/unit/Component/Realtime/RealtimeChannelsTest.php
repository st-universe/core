<?php

declare(strict_types=1);

namespace Stu\Component\Realtime;

use InvalidArgumentException;
use Noodlehaus\Config;
use PHPUnit\Framework\TestCase;

final class RealtimeChannelsTest extends TestCase
{
    public function testDefaultPreservesExistingKeys(): void
    {
        $channels = new RealtimeChannels(new Config([]));
        self::assertSame('stu:realtime:starmap:spacecraft', $channels->starmapSpacecraftStream());
        self::assertSame('stu:realtime:starmap:coverage:261:2', $channels->starmapCoverageKey(261, 2));
    }

    public function testInstancesUseSeparateStreamsAndCoverageKeys(): void
    {
        foreach (['stu', 'test_dev', 'test-01_local'] as $namespace) {
            $config = new Config([]);
            $config->set('realtime.redisNamespace', $namespace);
            $channels = new RealtimeChannels($config);
            self::assertSame($namespace . ':realtime:starmap:spacecraft', $channels->starmapSpacecraftStream());
            self::assertSame($namespace . ':realtime:starmap:coverage:261:2', $channels->starmapCoverageKey(261, 2));
        }
    }

    public function testInvalidNamespaceCannotSilentlyUseLiveKeys(): void
    {
        foreach (['', 'stu:dev', 'stu dev', "stu\n", "stu\r", str_repeat('a', 65), 123, false, []] as $namespace) {
            $config = new Config([]);
            $config->set('realtime.redisNamespace', $namespace);
            try {
                new RealtimeChannels($config);
                self::fail('Invalid namespace was accepted');
            } catch (InvalidArgumentException $exception) {
                self::assertSame('Invalid realtime.redisNamespace', $exception->getMessage());
            }
        }
    }
}
