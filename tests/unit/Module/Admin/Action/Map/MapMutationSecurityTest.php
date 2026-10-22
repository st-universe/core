<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map;

use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use ReflectionNamedType;
use request;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Session\SessionInterface;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\AccessCheck;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\GameUserRoleCheckerInterface;
use Stu\Module\PlayerSetting\Lib\UserStateEnum;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\MapBorderType;
use Stu\Orm\Entity\MapFieldType;
use Stu\Orm\Entity\MapRegion;
use Stu\Orm\Entity\Names;
use Stu\Orm\Entity\StarSystem;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\StarSystemType;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use Stu\StuTestCase;

final class MapMutationSecurityTest extends StuTestCase
{
    private ?string $previousMethod;

    #[\Override]
    protected function setUp(): void
    {
        $this->previousMethod = $_SERVER['REQUEST_METHOD'] ?? null;
    }

    #[\Override]
    protected function tearDown(): void
    {
        if ($this->previousMethod === null) {
            unset($_SERVER['REQUEST_METHOD']);
        } else {
            $_SERVER['REQUEST_METHOD'] = $this->previousMethod;
        }
        parent::tearDown();
    }

    public static function actions(): iterable
    {
        foreach (['EditAdminRegion', 'EditBorder', 'EditEffects', 'EditField', 'EditFullMapField', 'EditInfluenceArea', 'EditPassable', 'EditRegion', 'EditSystemField', 'EditSystemType', 'GenerateEmptySystems', 'RegenerateSystem', 'ResetEffects', 'SetIonStormMovement'] as $action) {
            yield $action => [$action];
        }
    }

    #[DataProvider('actions')]
    public function testGetDoesNotReadOrWriteAnyMapDataEvenWithOwnToken(string $action): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        request::setMockVars(['sstr' => 'own-token']);
        [$subject] = $this->createAction($action);
        self::assertTrue($subject->performSessionCheck());
        self::assertTrue($this->accessCheck(true)->checkUserAccess($subject, $this->mock(InformationInterface::class)));
        $subject->handle($this->mock(ActionControllerContext::class));
    }

    #[DataProvider('actions')]
    public function testMissingInvalidForeignAndConsumedTokensPreventDispatch(string $action): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        [$subject] = $this->createAction($action);
        foreach ([null, 'invalid-token', 'foreign-token', 'consumed-token'] as $token) {
            request::setMockVars($token === null ? [] : ['sstr' => $token]);
            self::assertFalse($this->accessCheck(false)->checkUserAccess($subject, $this->mock(InformationInterface::class)));
        }
    }

    public static function normalActions(): iterable
    {
        foreach (self::actions() as $key => [$action]) {
            if (!in_array($action, ['EditFullMapField', 'SetIonStormMovement'])) {
                yield $key => [$action];
            }
        }
    }

    #[DataProvider('normalActions')]
    public function testAuthorizedPostStillPerformsMapMutation(string $action): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        request::setMockVars(['sstr' => 'own-token', 'field' => 1, 'effects' => [], 'mode' => 'replace', 'layerid' => 1, 'systemid' => 1]);
        [$subject, $dependencies] = $this->createAction($action);
        $context = $this->mock(ActionControllerContext::class);
        $context->shouldReceive('setView')->once();
        self::assertTrue($this->accessCheck(true)->checkUserAccess($subject, $this->mock(InformationInterface::class)));
        if ($action === 'GenerateEmptySystems') {
            $dependencies['generateEmptySystems']->shouldReceive('generate')->with(1, $context)->once()->andReturn(10);
            $context->shouldReceive('getInfo->addInformation')->once();
        } elseif ($action === 'RegenerateSystem') {
            $system = $this->mock(StarSystem::class);
            $map = $this->mock(Map::class);
            $layer = $this->mock(\Stu\Orm\Entity\Layer::class);
            $name = $this->mock(Names::class);
            $dependencies['starSystemRepository']->shouldReceive('find')->with(1)->once()->andReturn($system);
            $system->shouldReceive('getMap')->andReturn($map);
            $map->shouldReceive('getLayer')->andReturn($layer);
            $layer->shouldReceive('isFinished')->andReturnFalse();
            $dependencies['namesRepository']->shouldReceive('getRandomFreeSystemNames')->with(1)->andReturn([$name]);
            $name->shouldReceive('getName')->andReturn('Testsystem');
            $dependencies['starSystemCreation']->shouldReceive('recreateStarSystem')->with($map, 'Testsystem')->once();
            $context->shouldReceive('getInfo->addInformation')->once();
        } else {
            $this->expectFieldMutation($action, $dependencies);
        }
        $subject->handle($context);
    }

    private function createAction(string $action): array
    {
        $class = __NAMESPACE__ . '\\' . $action . '\\' . $action;
        $reflection = new ReflectionClass($class);
        $dependencies = [];
        foreach ($reflection->getConstructor()->getParameters() as $parameter) {
            $type = $parameter->getType();
            self::assertInstanceOf(ReflectionNamedType::class, $type);
            $dependencies[$parameter->getName()] = $this->mock($type->getName());
        }
        return [$reflection->newInstanceArgs(array_values($dependencies)), $dependencies];
    }

    private function accessCheck(bool $valid): AccessCheck
    {
        $tokens = $this->mock(SessionStringRepositoryInterface::class);
        $session = $this->mock(SessionInterface::class);
        $user = $this->mock(User::class);
        $session->shouldReceive('getUser')->andReturn($user);
        $user->shouldReceive('getId')->andReturn(101);
        $user->shouldReceive('getState')->andReturn(UserStateEnum::ACTIVE);
        $tokens->shouldReceive('isValid')->withArgs(fn (string $token, int $id): bool => $id === 101 && $token !== '')->andReturn($valid);
        return new AccessCheck($tokens, $this->mock(StuConfigInterface::class), $this->mock(GameUserRoleCheckerInterface::class), $session);
    }

    private function expectFieldMutation(string $action, array $dependencies): void
    {
        $field = $this->mock($action === 'EditSystemField' ? StarSystemMap::class : Map::class);
        $mapRepository = $dependencies[$action === 'EditSystemField' ? 'starSystemMapRepository' : 'mapRepository'];
        $mapRepository->shouldReceive('find')->with(1)->once()->andReturn($field);
        foreach ($dependencies as $name => $dependency) {
            if (str_ends_with($name, 'Request')) {
                $dependency->shouldReceive('getFieldId')->andReturn(1);
                foreach (['getFieldType', 'getAdminRegionId', 'getBorder', 'getInfluenceAreaId', 'getPassable', 'getRegionId', 'getSystemType'] as $method) {
                    if (method_exists($dependency, $method)) {
                        $dependency->shouldReceive($method)->andReturn(2);
                    }
                }
            }
        }
        if (in_array($action, ['EditEffects', 'ResetEffects', 'EditPassable'])) {
            $type = $this->mock(MapFieldType::class);
            $field->shouldReceive('getFieldType')->andReturn($type);
            if ($action === 'EditPassable') {
                $type->shouldReceive('setPassable')->with(false)->once();
            } else {
                if ($action === 'EditEffects') {
                    $type->shouldReceive('getEffects')->andReturn([]);
                }
                $type->shouldReceive('setEffects')->with(null)->once();
            }
            $dependencies['mapFieldTypeRepository']->shouldReceive('save')->with($type)->once();
            return;
        }
        [$repository, $entityType, $setter] = match ($action) {
            'EditField', 'EditSystemField' => ['mapFieldTypeRepository', MapFieldType::class, 'setFieldType'],
            'EditRegion' => ['mapRegionRepository', MapRegion::class, 'setRegionId'],
            'EditAdminRegion' => ['mapRegionRepository', MapRegion::class, 'setAdminRegionId'],
            'EditBorder' => ['mapBorderTypeRepository', MapBorderType::class, 'setBordertypeId'],
            'EditSystemType' => ['starSystemTypeRepository', StarSystemType::class, 'setSystemTypeId'],
            default => ['starSystemRepository', StarSystem::class, 'setInfluenceArea']
        };
        $entity = $this->mock($entityType);
        $entity->shouldReceive('getId')->andReturn(2);
        $dependencies[$repository]->shouldReceive('find')->with(2)->once()->andReturn($entity);
        $field->shouldReceive($setter)->with(in_array($setter, ['setFieldType', 'setInfluenceArea']) ? $entity : 2)->once();
        $mapRepository->shouldReceive('save')->with($field)->once();
    }
}
