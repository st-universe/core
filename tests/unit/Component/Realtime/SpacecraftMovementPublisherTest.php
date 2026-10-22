<?php

declare(strict_types=1);

namespace Stu\Component\Realtime;

use JBBCode\Parser;
use Noodlehaus\Config;
use Stu\Module\Config\StuConfigInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

final class SpacecraftMovementPublisherTest extends StuTestCase
{
    public function testDeletingAnAlreadyInvisibleSpacecraftDoesNotReadOrPublishItsPosition(): void
    {
        $spacecraft = $this->mock(Spacecraft::class);
        $spacecraft->shouldReceive('isRpgModuleInvisible')->once()->andReturn(true);
        $spacecraft->shouldNotReceive('getLocation');
        $publisher = new SpacecraftMovementPublisher(
            new RealtimeRedisFactory($this->mock(StuConfigInterface::class)),
            new Parser(),
            new RealtimeChannels(new Config([])),
            $this->mock(UserRepositoryInterface::class),
            $this->mock(StuConfigInterface::class)
        );
        $publisher->publishRemoval($spacecraft);
    }
    public function testCoverageInvalidationTakesTheExistingOwnerLockBeforeRedisPublication(): void
    {
        $spacecraft = $this->mock(Spacecraft::class);
        $map = $this->mock(\Stu\Orm\Entity\Map::class);
        $layer = $this->mock(\Stu\Orm\Entity\Layer::class);
        $owner = $this->mock(\Stu\Orm\Entity\User::class);
        $owners = $this->mock(UserRepositoryInterface::class);
        $config = $this->mock(StuConfigInterface::class);
        $db = $this->mock(\Stu\Module\Config\Model\DbSettingsInterface::class);
        $cache = $this->mock(\Stu\Module\Config\Model\CacheSettingsInterface::class);
        $spacecraft->shouldReceive('getLocation')->andReturn($map);
        $spacecraft->shouldReceive('getUser')->andReturn($owner);
        $owner->shouldReceive('getId')->andReturn(101);
        $map->shouldReceive('getLayer')->andReturn($layer);
        $map->shouldReceive('getX')->andReturn(5);
        $map->shouldReceive('getY')->andReturn(5);
        $map->shouldReceive('getFieldType')->andReturn(new \Stu\Orm\Entity\MapFieldType());
        $layer->shouldReceive('getId')->andReturn(2);
        $config->shouldReceive('getDbSettings')->andReturn($db);
        $db->shouldReceive('useSqlite')->andReturn(false);
        $owners->shouldReceive('lockUsersForUpdate')->with([101])->once()->globally()->ordered();
        $config->shouldReceive('getCacheSettings')->once()->globally()->ordered()->andReturn($cache);
        $cache->shouldReceive('getRedisSocket')->andReturn(null);
        $cache->shouldReceive('getRedisHost')->andReturn('127.0.0.1');
        $cache->shouldReceive('getRedisPort')->andReturn(1);
        $publisher = new SpacecraftMovementPublisher(new RealtimeRedisFactory($config), new Parser(), new RealtimeChannels(new Config([])), $owners, $config);
        $publisher->publishCoverageChanged($spacecraft);
    }

}
