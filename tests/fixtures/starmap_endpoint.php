<?php

declare(strict_types=1);

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use JBBCode\Parser;
use Noodlehaus\Config;
use Stu\Component\Realtime\RealtimeChannels;
use Stu\Component\Realtime\RealtimeRedisFactory;
use Stu\Component\Realtime\StarmapRealtimeTokenFactory;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Module\Config\Model\CacheSettingsInterface;
use Stu\Module\Config\Model\DbSettingsInterface;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Message\Lib\ContactListModeEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
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
use Stu\Orm\Repository\UserRepositoryInterface;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

putenv('STU_REALTIME_SECRET=' . str_repeat('test-key-', 8));
register_shutdown_function(static fn () => Mockery::close());
$mode = $argv[1];
$config = new Config([]);
$factory = new StarmapRealtimeTokenFactory($config);
$user = Mockery::mock(User::class);
$user->shouldReceive('getId')->andReturn(101);
$user->shouldReceive('getAlliance')->andReturn(null);
$user->shouldReceive('getUserLayers')->andReturn(new ArrayCollection([2 => null]));
$game = Mockery::mock(ViewControllerContext::class);
$game->shouldReceive('getUser')->andReturn($user);

if (str_starts_with($mode, 'scan')) {
    request::setMockVars(['connectionid' => $mode === 'scan' ? 'test-connection' : '']);
    $game->shouldReceive('getSessionString')->andReturn('test-session');
    $game->shouldReceive('getViewContextMetadata')->andReturn(json_encode([
        'success' => true, 'layerId' => 2, 'sourceId' => 777, 'x' => 5, 'y' => 5,
        'spacecrafts' => [['id' => 503, 'name' => 'Gescanntes Schiff', 'userId' => 103, 'x' => 5, 'y' => 5, 'isEnemy' => true]]
    ], JSON_THROW_ON_ERROR));
    $em = Mockery::mock(EntityManagerInterface::class);
    $em->shouldReceive('flush')->once();
    $em->shouldReceive('commit')->once();
    new ShowUserStarmapScan($em, $factory)->handle($game);
}

request::setMockVars(['layerid' => 2]);
$layer = Mockery::mock(Layer::class);
$layer->shouldReceive('getId')->andReturn(2);
$layer->shouldReceive('getWidth')->andReturn(10);
$layer->shouldReceive('getHeight')->andReturn(10);
$layers = Mockery::mock(LayerRepositoryInterface::class);
$layers->shouldReceive('find')->with(2)->andReturn($layer);
$contacts = Mockery::mock(ContactRepositoryInterface::class);
$friend = Mockery::mock(Contact::class);
$friend->shouldReceive('getRecipientId')->andReturn(102);
$friend->shouldReceive('getMode')->andReturn(ContactListModeEnum::FRIEND);
$contacts->shouldReceive('getOrderedByUser')->andReturn([$friend]);
$contacts->shouldReceive('getByRecipient')->andReturn([]);
$relations = Mockery::mock(RelationRepositoryInterface::class);
$relations->shouldReceive('getByUserAndAlliance')->andReturn([]);
$maps = Mockery::mock(MapRepositoryInterface::class);
$maps->shouldReceive('getLssBlockadeLocations')->andReturn([]);
$spacecrafts = Mockery::mock(SpacecraftRepositoryInterface::class);
$ranges = [['source_id' => 777, 'x' => 5, 'y' => 5, 'sensor_range' => 1, 'tachyon_range' => 1]];
if ($mode === 'realtime-locked') {
    $spacecrafts->shouldReceive('getUserStarmapRealtimeSensorRanges')->once()->globally()->ordered()->andReturn($ranges);
    $spacecrafts->shouldReceive('getUserIdsForSpacecrafts')->with([777])->once()->globally()->ordered()->andReturn([201, 101]);
} else {
    $spacecrafts->shouldReceive('getUserStarmapRealtimeSensorRanges')->once()->andReturn($ranges);
}
$userRepository = Mockery::mock(UserRepositoryInterface::class);
if ($mode === 'realtime-locked') {
    $userRepository->shouldReceive('lockUsersForUpdate')->with([101, 201])->once()->globally()->ordered();
    $spacecrafts->shouldReceive('getUserStarmapRealtimeSensorRanges')->once()->globally()->ordered()->andReturn($ranges);
}
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
$stuConfig = Mockery::mock(StuConfigInterface::class);
$cache = Mockery::mock(CacheSettingsInterface::class);
$cache->shouldReceive('getRedisSocket')->andReturn(null);
$cache->shouldReceive('getRedisHost')->andReturn('127.0.0.1');
$cache->shouldReceive('getRedisPort')->andReturn(1);
$stuConfig->shouldReceive('getCacheSettings')->andReturn($cache);
$db = Mockery::mock(DbSettingsInterface::class);
$db->shouldReceive('useSqlite')->andReturn($mode !== 'realtime-locked');
$stuConfig->shouldReceive('getDbSettings')->andReturn($db);

new ShowUserStarmapRealtime(
    $layers,
    $spacecrafts,
    $factory,
    new RealtimeRedisFactory($stuConfig),
    $config,
    Mockery::mock(AllianceJobManagerInterface::class),
    new StarmapSensorData(new Parser(), $relations, $contacts, $maps),
    Mockery::mock(SpacecraftWrapperFactoryInterface::class),
    new RealtimeChannels($config),
    $userRepository,
    $stuConfig
)->handle($game);
