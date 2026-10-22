<?php

declare(strict_types=1);

namespace Stu\Action\Admin;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use request;
use Stu\Component\Game\ModuleEnum;
use Stu\IntegrationTestCase;
use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Lib\Session\SessionStringFactory;
use Stu\Module\Control\AccessCheckInterface;
use Stu\Module\Control\Component\Action\ActionContextFactoryInterface;
use Stu\Module\Control\Component\CallbackExecution;
use Stu\Module\Control\Component\ControllerDiscoveryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\GameData;
use Stu\Module\Control\GameModuleAccessCheckerInterface;
use Stu\Module\Control\StuTime;
use Stu\Orm\Entity\GameRequest;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\MapRegion;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class MapMutationIntegrationTest extends IntegrationTestCase
{
    private ?string $previousMethod;
    private int $regionId;
    private int $systemFieldId;

    #[\Override]
    public function setUp(): void
    {
        self::$isSchemaInitializationNeeded = true;
        parent::setUp();
        self::$isSchemaInitializationNeeded = true;
        self::$testSession->setUserById(102);
        self::assertTrue($this->getContainer()->get(GameModuleAccessCheckerInterface::class)->isAllowed(ModuleEnum::ADMIN));
        $this->previousMethod = $_SERVER['REQUEST_METHOD'] ?? null;
        request::setMockVars(null);
        $_GET = [];
        $_POST = [];
        $em = $this->getContainer()->get(EntityManagerInterface::class);
        $region = new MapRegion()->setDescription('Neue Testregion');
        $em->persist($region);
        $map = $em->find(Map::class, 14401);
        $map->getFieldType()->setPassable(true)->setEffects([FieldTypeEffectEnum::CLOAK_UNUSEABLE]);
        $em->flush();
        $this->regionId = $region->getId();
        $this->systemFieldId = $em->getRepository(StarSystemMap::class)->findOneBy([])->getId();
    }

    #[\Override]
    public function tearDown(): void
    {
        $_GET = [];
        $_POST = [];
        request::setMockVars(null);
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

    public static function normalActions(): iterable
    {
        foreach (self::actions() as $key => [$action]) {
            if (!in_array($action, ['GenerateEmptySystems', 'RegenerateSystem', 'EditFullMapField', 'SetIonStormMovement'])) {
                yield $key => [$action];
            }
        }
    }

    #[DataProvider('actions')]
    public function testForgedRequestsCannotChangePersistedMapState(string $action): void
    {
        $before = $this->state();
        foreach ([['GET', 'missing'], ['GET', 'own'], ['POST', 'missing'], ['POST', 'wrong'], ['POST', 'foreign'], ['POST', 'consumed']] as [$method, $kind]) {
            $parameters = $this->parameters($action);
            if ($kind !== 'missing') {
                $parameters['sstr'] = $this->token($kind === 'foreign' ? 101 : 102);
                if ($kind === 'wrong') {
                    $parameters['sstr'] = 'ungültiger-token';
                } elseif ($kind === 'consumed') {
                    self::assertTrue($this->getContainer()->get(SessionStringRepositoryInterface::class)->isValid($parameters['sstr'], 102));
                }
            }
            $this->dispatch($action, $method, $parameters);
            self::assertSame($before, $this->state(), $action . ' ' . $method . ' ' . $kind);
        }
    }

    #[DataProvider('normalActions')]
    public function testAuthorizedPostChangesDatabaseAndConsumedTokenCannotBeReused(string $action): void
    {
        $before = $this->state();
        $parameters = $this->parameters($action) + ['sstr' => $this->token(102)];
        $this->dispatch($action, 'POST', $parameters);
        $after = $this->state();
        self::assertNotSame($before, $after);
        match ($action) {
            'EditAdminRegion' => self::assertSame($this->regionId, $after['adminRegion']),
            'EditBorder' => self::assertSame(1, $after['border']),
            'EditEffects' => self::assertSame(['WARPDRIVE_LEAK'], $after['effects']),
            'EditField' => self::assertSame(203, $after['fieldType']),
            'EditInfluenceArea' => self::assertNull($after['influence']),
            'EditPassable' => self::assertFalse($after['passable']),
            'EditRegion' => self::assertSame($this->regionId, $after['region']),
            'EditSystemField' => self::assertSame(90122, $after['systemFieldType']),
            'EditSystemType' => self::assertSame(1060, $after['systemType']),
            'ResetEffects' => self::assertSame([], $after['effects'])
        };
        $this->dispatch($action, 'POST', $parameters);
        self::assertSame($after, $this->state());
        self::assertFalse($this->getContainer()->get(SessionStringRepositoryInterface::class)->isValid($parameters['sstr'], 102));
    }

    private function parameters(string $action): array
    {
        return match ($action) {
            'EditAdminRegion' => ['field' => 14401, 'adminregion' => $this->regionId],
            'EditBorder' => ['field' => 14401, 'border' => 1],
            'EditEffects' => ['field' => 14401, 'mode' => 'replace', 'effects' => ['WARPDRIVE_LEAK']],
            'EditField' => ['field' => 14401, 'type' => 203],
            'EditInfluenceArea' => ['field' => 14401, 'area' => 9999],
            'EditPassable' => ['field' => 14401, 'passable' => 2],
            'EditRegion' => ['field' => 14401, 'region' => $this->regionId],
            'EditSystemField' => ['field' => $this->systemFieldId, 'type' => 90122],
            'EditSystemType' => ['field' => 14401, 'type' => 1060],
            'ResetEffects' => ['field' => 14401],
            'GenerateEmptySystems' => ['layerid' => 2],
            'RegenerateSystem' => ['systemid' => 252],
            'EditFullMapField' => ['field' => 14401, 'operation' => 'region', 'value' => 0],
            default => ['rootId' => 1, 'directionInDegrees' => '90', 'velocity' => '2', 'movementType' => '1']
        };
    }

    private function token(int $userId): string
    {
        $dic = $this->getContainer();
        return new SessionStringFactory($dic->get(SessionStringRepositoryInterface::class), new StuTime())
            ->createSessionString($dic->get(UserRepositoryInterface::class)->find($userId));
    }

    private function dispatch(string $action, string $method, array $parameters): void
    {
        $class = 'Stu\\Module\\Admin\\Action\\Map\\' . $action . '\\' . $action;
        $_SERVER['REQUEST_METHOD'] = $method;
        $parameters[$class::ACTION_IDENTIFIER] = '1';
        $_GET = $method === 'GET' ? $parameters : [];
        $_POST = $method === 'POST' ? $parameters : [];
        $dic = $this->getContainer();
        $arguments = [];
        foreach (new ReflectionClass($class)->getConstructor()->getParameters() as $parameter) {
            $type = $parameter->getType()->getName();
            $arguments[] = str_ends_with($type, 'RequestInterface')
                ? new (substr($type, 0, -strlen('Interface')))()
                : $dic->get($type);
        }
        $controller = new $class(...$arguments);
        $discovery = $this->mock(ControllerDiscoveryInterface::class);
        $discovery->shouldReceive('getControllers')->with(ModuleEnum::ADMIN, false)->once()->andReturn([$class::ACTION_IDENTIFIER => $controller]);
        $data = new GameData();
        $gameRequest = new GameRequest();
        $game = $this->mock(GameControllerInterface::class);
        $game->shouldReceive('getGameRequest')->andReturn($gameRequest);
        $game->shouldReceive('getGameData')->andReturn($data);
        $game->shouldReceive('getInfo')->andReturn($data->gameInformations);
        $game->shouldReceive('getUser')->andReturn(self::$testSession->getUser());
        new CallbackExecution($discovery, $dic->get(AccessCheckInterface::class), $dic->get(ActionContextFactoryInterface::class), new StuTime(), $dic->get(EntityManagerInterface::class))
            ->execute(ModuleEnum::ADMIN, $game);
        self::assertSame($class::ACTION_IDENTIFIER, $gameRequest->getAction());
    }

    private function state(): array
    {
        $em = $this->getContainer()->get(EntityManagerInterface::class);
        $map = $em->find(Map::class, 14401);
        $em->refresh($map);
        $fieldType = $map->getFieldType();
        $em->refresh($fieldType);
        $systemField = $em->find(StarSystemMap::class, $this->systemFieldId);
        $em->refresh($systemField);
        return [
            'fieldType' => $fieldType->getId(),
            'passable' => $fieldType->getPassable(),
            'effects' => array_map(fn (FieldTypeEffectEnum $effect): string => $effect->value, $fieldType->getEffects()),
            'region' => $map->getRegionId(),
            'adminRegion' => $map->getAdminRegionId(),
            'border' => $map->getBordertypeId(),
            'influence' => $map->getInfluenceAreaId(),
            'systemType' => $map->getSystemTypeId(),
            'systemFieldType' => $systemField->getFieldType()->getId()
        ];
    }
}
