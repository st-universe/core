<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\View;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use JBBCode\Parser;
use Noodlehaus\Config;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use request;
use Stu\Component\Realtime\RealtimeChannels;
use Stu\Component\Realtime\RealtimeRedisFactory;
use Stu\Component\Realtime\StarmapRealtimeTokenFactory;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Module\Config\Model\CacheSettingsInterface;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Message\Lib\ContactListModeEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Starmap\Lib\StarmapRelations;
use Stu\Module\Starmap\Lib\StarmapSensorCoverage;
use Stu\Module\Starmap\Lib\StarmapSensorData;
use Stu\Module\Starmap\View\ShowUserStarmapRealtime\ShowUserStarmapRealtime;
use Stu\Module\Starmap\View\ShowUserStarmapScan\ShowUserStarmapScan;
use Stu\Orm\Entity\Contact;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\RelationRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\StuTestCase;

final class StarmapEndpointTest extends StuTestCase
{
    #[RunInSeparateProcess]
    public function testRealtimeReleasesTheSessionBeforeCalculatingCoverage(): void
    {
        $directory = sys_get_temp_dir() . '/stu-starmap-session-' . bin2hex(random_bytes(8));
        mkdir($directory, 0700);
        try {
            session_start(['save_path' => $directory, 'use_cookies' => false, 'cache_limiter' => '']);
            $_SESSION['uid'] = 101;
            $this->endpoint('realtime');
            self::assertSame(PHP_SESSION_NONE, session_status());
        } finally {
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }
            unlink($directory . '/sess_' . session_id());
            rmdir($directory);
        }
    }

    public function testRealtimeJsonUsesOpaqueIdsButRetainsAuthorizedOwnAndFriendlyIds(): void
    {
        $first = $this->endpoint('realtime');
        $second = $this->endpoint('realtime');
        self::assertSame([501, 502], array_column(array_slice($first['spacecrafts'], 0, 2), 'id'));
        foreach ([2, 3] as $index) {
            self::assertLessThan(0, $first['spacecrafts'][$index]['id']);
            self::assertNotSame($first['spacecrafts'][$index]['id'], $second['spacecrafts'][$index]['id']);
            self::assertSame(0, $first['spacecrafts'][$index]['userId']);
        }
        self::assertSame([501, 502, 0, 0], array_map(static fn (array $spacecraft): int => $spacecraft['isOwn'] || $spacecraft['isFriendly'] ? $spacecraft['id'] : 0, $first['spacecrafts']));
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
        $previousSecret = getenv('STU_REALTIME_SECRET');
        putenv('STU_REALTIME_SECRET=' . str_repeat('test-key-', 8));

        try {
            $config = new Config([]);
            $factory = new StarmapRealtimeTokenFactory($config);
            $user = $this->mock(User::class);
            $user->shouldReceive('getId')->andReturn(101);
            $user->shouldReceive('getAlliance')->andReturn(null);
            $user->shouldReceive('getUserLayers')->andReturn(new ArrayCollection([2 => null]));
            $game = $this->mock(ViewControllerContext::class);
            $game->shouldReceive('getUser')->andReturn($user);

            if (str_starts_with($mode, 'scan')) {
                request::setMockVars(['connectionid' => $mode === 'scan' ? 'test-connection' : '']);
                $game->shouldReceive('getSessionString')->andReturn('test-session');
                $game->shouldReceive('getViewContextMetadata')->andReturn(json_encode([
                    'success' => true, 'layerId' => 2, 'sourceId' => 777, 'x' => 5, 'y' => 5,
                    'spacecrafts' => [['id' => 503, 'name' => 'Gescanntes Schiff', 'userId' => 103, 'x' => 5, 'y' => 5, 'isEnemy' => true]]
                ], JSON_THROW_ON_ERROR));
                $em = $this->mock(EntityManagerInterface::class);
                $em->shouldReceive('flush')->once();
                $em->shouldReceive('commit')->once();
                $subject = new ShowUserStarmapScan($em, $factory);
                return json_decode($this->getMethod($subject, 'getResponse')->invoke($subject, $game), true, 512, JSON_THROW_ON_ERROR);
            }

            request::setMockVars(['layerid' => 2]);
            $layer = $this->mock(Layer::class);
            $layer->shouldReceive('getId')->andReturn(2);
            $layer->shouldReceive('getWidth')->andReturn(10);
            $layer->shouldReceive('getHeight')->andReturn(10);
            $layers = $this->mock(LayerRepositoryInterface::class);
            $layers->shouldReceive('find')->with(2)->andReturn($layer);
            $contacts = $this->mock(ContactRepositoryInterface::class);
            $friend = $this->mock(Contact::class);
            $friend->shouldReceive('getRecipientId')->andReturn(102);
            $friend->shouldReceive('getMode')->andReturn(ContactListModeEnum::FRIEND);
            $contacts->shouldReceive('getOrderedByUser')->andReturn([$friend]);
            $contacts->shouldReceive('getByRecipient')->andReturn([]);
            $relations = $this->mock(RelationRepositoryInterface::class);
            $relations->shouldReceive('getByUserAndAlliance')->andReturn([]);
            $maps = $this->mock(MapRepositoryInterface::class);
            $em = $this->mock(EntityManagerInterface::class);
            $em->shouldReceive('flush')->once()->globally()->ordered();
            $em->shouldReceive('commit')->once()->globally()->ordered();
            $maps->shouldReceive('getLssBlockadeLocations')->once()->globally()->ordered()->andReturnUsing(static function (): array {
                self::assertSame(PHP_SESSION_NONE, session_status());
                return [];
            });
            $spacecrafts = $this->mock(SpacecraftRepositoryInterface::class);
            $ranges = [['source_id' => 777, 'x' => 5, 'y' => 5, 'sensor_range' => 1, 'tachyon_range' => 1]];
            $spacecrafts->shouldReceive('getUserStarmapRealtimeSensorRanges')->once()->andReturn($ranges);
            $spacecrafts->shouldNotReceive('getUserIdsForSpacecrafts');
            $rows = [];
            foreach ([101, 102, 103, 104] as $index => $owner) {
                $rows[] = [
                    'id' => 501 + $index, 'user_id' => $owner, 'alliance_id' => null, 'is_cloaked' => $owner === 104,
                    'alert_state' => 1, 'name' => 'Testschiff ' . $index, 'type' => 'SHIP', 'user_name' => 'Testspieler ' . $owner,
                    'alliance_name' => null, 'rump_id' => 34, 'rump_name' => 'Testrumpf', 'x' => 5, 'y' => 5, 'in_system' => false,
                    'system_name' => null, 'hull' => 100, 'max_hull' => 100, 'shield' => 0, 'max_shield' => 0, 'eps' => 20,
                    'max_eps' => 100, 'warpdrive' => 20, 'max_warpdrive' => 100,
                    'field_effects' => $mode === 'realtime-concealed' ? '["DUBIOUS_SPACECRAFT_COUNT"]' : ''
                ];
            }
            $spacecrafts->shouldReceive('getUserStarmapRealtimeSpacecrafts')->andReturn($rows);
            $spacecrafts->shouldReceive('find')->with(777)->andReturn(null);
            $stuConfig = $this->mock(StuConfigInterface::class);
            $cache = $this->mock(CacheSettingsInterface::class);
            $cache->shouldReceive('getRedisSocket')->andReturn(null);
            $cache->shouldReceive('getRedisHost')->andReturn('127.0.0.1');
            $cache->shouldReceive('getRedisPort')->andReturn(1);
            $stuConfig->shouldReceive('getCacheSettings')->andReturn($cache);

            $starmapRelations = new StarmapRelations($relations, $contacts);

            $subject = new ShowUserStarmapRealtime(
                $layers,
                $spacecrafts,
                $factory,
                new RealtimeRedisFactory($stuConfig),
                $config,
                $this->mock(AllianceJobManagerInterface::class),
                new StarmapSensorData(new Parser(), $starmapRelations),
                new StarmapSensorCoverage($maps),
                $starmapRelations,
                $this->mock(SpacecraftWrapperFactoryInterface::class),
                new RealtimeChannels($config),
                $em
            );

            return json_decode($this->getMethod($subject, 'getResponse')->invoke($subject, $game), true, 512, JSON_THROW_ON_ERROR);
        } finally {
            putenv($previousSecret === false ? 'STU_REALTIME_SECRET' : 'STU_REALTIME_SECRET=' . $previousSecret);
        }
    }
}
