<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\View;

use PHPUnit\Framework\TestCase;

final class StarmapEndpointTest extends TestCase
{
    public function testRealtimeJsonUsesOpaqueIdsButRetainsAuthorizedOwnAndFriendlyIds(): void
    {
        foreach (['realtime', 'realtime-locked'] as $mode) {
            $first = $this->endpoint($mode);
            $second = $this->endpoint($mode);
            self::assertSame([501, 502], array_column(array_slice($first['spacecrafts'], 0, 2), 'id'));
            foreach ([2, 3] as $index) {
                self::assertLessThan(0, $first['spacecrafts'][$index]['id']);
                self::assertNotSame($first['spacecrafts'][$index]['id'], $second['spacecrafts'][$index]['id']);
                self::assertSame(0, $first['spacecrafts'][$index]['userId']);
            }
            self::assertSame([501, 502, 0, 0], array_map(static fn (array $spacecraft): int => $spacecraft['isOwn'] || $spacecraft['isFriendly'] ? $spacecraft['id'] : 0, $first['spacecrafts']));
        }
    }

    public function testScanJsonHidesInternalIdsInsideAuthenticatedEncryptionWithOrWithoutAConnection(): void
    {
        foreach (['scan', 'scan-no-connection'] as $mode) {
            $response = $this->endpoint($mode);
            $result = $response['result'];
            self::assertSame('test-session', $response['sessionString']);
            self::assertLessThan(0, $result['spacecrafts'][0]['id']);
            self::assertSame('Gescanntes Schiff', $result['spacecrafts'][0]['name']);
            [$encoded] = explode('.', $result['trackingToken']);
            $claims = json_decode(base64_decode(strtr($encoded, '-_', '+/')), true, 512, JSON_THROW_ON_ERROR);
            self::assertArrayNotHasKey('scan', $claims);
            self::assertSame($mode === 'scan' ? 'test-connection' : '', $claims['connectionId']);
            $encrypted = base64_decode($claims['encryptedScan'], true);
            self::assertStringNotContainsString('"id":503', $encrypted);
            $scan = openssl_decrypt(substr($encrypted, 28), 'aes-256-gcm', hash('sha256', str_repeat('test-key-', 8), true), OPENSSL_RAW_DATA, substr($encrypted, 0, 12), substr($encrypted, 12, 16));
            self::assertIsString($scan);
            self::assertSame(503, json_decode($scan, true, 512, JSON_THROW_ON_ERROR)['spacecrafts'][0]['id']);
        }
    }

    public function testDubiousJsonContainsOneFieldMarkerAndNoIndividualForeignContacts(): void
    {
        $response = $this->endpoint('realtime-concealed');
        self::assertSame([501], array_column($response['spacecrafts'], 'id'));
        self::assertSame([['x' => 5, 'y' => 5]], $response['dubiousFields']);
    }

    private function endpoint(string $mode): array
    {
        $process = proc_open([PHP_BINARY, dirname(__DIR__, 4) . '/fixtures/starmap_endpoint.php', $mode], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        self::assertIsResource($process);
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        self::assertSame(0, proc_close($process), $errors);
        return json_decode($output, true, 512, JSON_THROW_ON_ERROR);
    }
}
