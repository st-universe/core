<?php

declare(strict_types=1);

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Stu\Component\Spacecraft\Buildplan\BuildplanSignatureCreationInterface;
use Stu\Component\Spacecraft\Crew\SpacecraftCrewCalculatorInterface;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Config\Init;
use Stu\Config\SessionStarterInterface;
use Stu\Exception\SessionInvalidException;
use Stu\Lib\Session\SessionInterface;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\AdminSessionGuard;
use Stu\Module\Control\AdminSessionGuardInterface;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Module\Spacecraft\Lib\Creation\SpacecraftConfiguratorInterface;
use Stu\Orm\Entity\BuildplanModule;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\ShipRumpModuleLevel;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\BuildplanModuleRepositoryInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\Orm\Repository\ShipRumpModuleLevelRepositoryInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

require dirname(__DIR__, 4) . '/Bootstrap.php';

[$helper, $role, $operation] = array_slice($argv, 1);
$_GET = [];
$_POST = $operation === 'create' ? [
    'userId' => 7,
    'buildplanId' => 11,
    'rumpId' => 12,
    'layer' => 1,
    'cx' => 2,
    'cy' => 3,
    'shipcount' => 1,
    'noTorps' => 1,
    'mod' => [SpacecraftModuleTypeEnum::HULL->value => 21]
] : [];
$_SERVER['REQUEST_METHOD'] = $operation === 'create' ? 'POST' : 'GET';

$starter = Mockery::mock(SessionStarterInterface::class);
$starter->shouldReceive('start')->once();
$session = Mockery::mock(SessionInterface::class);
$config = Mockery::mock(StuConfigInterface::class);
if ($role === 'no-session') {
    $session->shouldReceive('createSession')->with(true)->once()->andThrow(new SessionInvalidException());
} else {
    $session->shouldReceive('createSession')->with(true)->once();
    $user = $role === 'missing-user' ? null : Mockery::mock(User::class);
    $user?->shouldReceive('getId')->once()->andReturn($role === 'admin' ? 42 : 43);
    $session->shouldReceive('getUser')->once()->andReturn($user);
    $config->shouldReceive('getGameSettings->getAdminIds')->once()->andReturn([42]);
}

$container = Mockery::mock(ContainerInterface::class);
$container->shouldReceive('get')->with(AdminSessionGuardInterface::class)->once()
    ->andReturn(new AdminSessionGuard($starter, $session, $config));

if ($role === 'admin') {
    $services = [];
    foreach ([
        EntityManagerInterface::class,
        SpacecraftBuildplanRepositoryInterface::class,
        TorpedoTypeRepositoryInterface::class,
        UserRepositoryInterface::class,
        ShipCreatorInterface::class,
        MapRepositoryInterface::class,
        LayerRepositoryInterface::class,
        SpacecraftRumpRepositoryInterface::class,
        ShipRumpModuleLevelRepositoryInterface::class,
        ModuleRepositoryInterface::class,
        BuildplanModuleRepositoryInterface::class,
        SpacecraftCrewCalculatorInterface::class,
        BuildplanSignatureCreationInterface::class
    ] as $service) {
        $services[$service] = Mockery::mock($service);
        $container->shouldReceive('get')->with($service)->andReturn($services[$service]);
    }

    $db = $services[EntityManagerInterface::class];
    $db->shouldReceive('beginTransaction')->once();
    $db->shouldReceive('commit')->once();

    if ($operation === 'list') {
        $services[UserRepositoryInterface::class]->shouldReceive('getNpcList')->once()->andReturn([]);
        $services[UserRepositoryInterface::class]->shouldReceive('getNonNpcList')->once()->andReturn([]);
    } else {
        $rump = Mockery::mock(SpacecraftRump::class);
        $rump->shouldReceive('getId')->andReturn(12);

        if ($helper === 'createShip') {
            $plan = Mockery::mock(SpacecraftBuildplan::class);
            $plan->shouldReceive('getRump')->once()->andReturn($rump);
            $plan->shouldReceive('getId')->once()->andReturn(11);
            $services[SpacecraftBuildplanRepositoryInterface::class]->shouldReceive('find')->with(11)->once()->andReturn($plan);
            $layer = Mockery::mock(Layer::class);
            $services[LayerRepositoryInterface::class]->shouldReceive('find')->with(1)->once()->andReturn($layer);
            $map = Mockery::mock(Map::class);
            $services[MapRepositoryInterface::class]->shouldReceive('getByCoordinates')->with($layer, 2, 3)->once()->andReturn($map);
            $configurator = Mockery::mock(SpacecraftConfiguratorInterface::class);
            $services[ShipCreatorInterface::class]->shouldReceive('createBy')->with(7, 12, 11)->once()->andReturn($configurator);
            $configurator->shouldReceive('setLocation')->with($map)->once()->andReturnSelf();
            $configurator->shouldReceive('maxOutSystems')->once()->andReturnSelf();
            $configurator->shouldReceive('createCrew')->once()->andReturnSelf();
            $configurator->shouldReceive('setTorpedo')->with(null)->once()->andReturnSelf();
            $configurator->shouldReceive('finishConfiguration')->once();
            $db->shouldReceive('flush')->once();
        } else {
            $services[SpacecraftRumpRepositoryInterface::class]->shouldReceive('find')->with(12)->once()->andReturn($rump);
            $rump->shouldReceive('getCategoryId')->once()->andReturn(SpacecraftRumpCategoryEnum::STATION);
            $rump->shouldReceive('getName')->once()->andReturn('Testrumpf');
            $level = Mockery::mock(ShipRumpModuleLevel::class);
            $level->shouldReceive('getMandatoryModulesCount')->once()->andReturn(1);
            $services[ShipRumpModuleLevelRepositoryInterface::class]->shouldReceive('getByShipRump')->with($rump)->once()->andReturn($level);
            $recipient = Mockery::mock(User::class);
            $services[UserRepositoryInterface::class]->shouldReceive('find')->with(7)->once()->andReturn($recipient);
            $services[BuildplanSignatureCreationInterface::class]->shouldReceive('createSignatureByModuleIds')->with([21], 0)->once()->andReturn('test-signature');
            $services[SpacecraftBuildplanRepositoryInterface::class]->shouldReceive('getByUserShipRumpAndSignature')->with(7, 12, 'test-signature')->once()->andReturn(null);
            $plan = new SpacecraftBuildplan();
            $services[SpacecraftBuildplanRepositoryInterface::class]->shouldReceive('prototype')->once()->andReturn($plan);
            $services[SpacecraftBuildplanRepositoryInterface::class]->shouldReceive('save')->with($plan)->twice();
            $module = new Module();
            $module->setType(SpacecraftModuleTypeEnum::HULL);
            $services[ModuleRepositoryInterface::class]->shouldReceive('find')->with(21)->once()->andReturn($module);
            $services[BuildplanModuleRepositoryInterface::class]->shouldReceive('prototype')->once()->andReturn(new BuildplanModule());
            $services[BuildplanModuleRepositoryInterface::class]->shouldReceive('save')->once();
            $services[SpacecraftCrewCalculatorInterface::class]->shouldReceive('getCrewUsage')->with([21 => $module], $rump, $recipient)->once()->andReturn(5);
            $db->shouldReceive('flush')->twice();
        }
    }
}

final class AdminHelperTestInit
{
    public static ContainerInterface $container;

    public static function run(callable $callback): void
    {
        $callback(self::$container);
    }
}

AdminHelperTestInit::$container = $container;
class_alias(AdminHelperTestInit::class, Init::class);
register_shutdown_function(static fn () => Mockery::close());

try {
    require dirname(__DIR__, 5) . '/src/Public/admin/help/' . $helper . '.php';
    echo 'COMPLETED';
} catch (SessionInvalidException) {
    echo 'SESSION_INVALID';
}
