<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\Action\ScanUserStarmap;

use Doctrine\Common\Collections\ArrayCollection;
use JBBCode\Parser;
use Mockery\MockInterface;
use request;
use stdClass;
use Stu\Component\Spacecraft\System\Data\EpsSystemData;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Starmap\Lib\StarmapRelations;
use Stu\Module\Starmap\Lib\StarmapSensorCoverage;
use Stu\Module\Starmap\Lib\StarmapSensorData;
use Stu\Module\Station\Lib\StationLoaderInterface;
use Stu\Module\Station\Lib\StationWrapperInterface;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\RelationRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\StuTestCase;

class ScanUserStarmapTest extends StuTestCase
{
    private SpacecraftRepositoryInterface&MockInterface $spacecraftRepository;
    private MapRepositoryInterface&MockInterface $mapRepository;
    private StationLoaderInterface&MockInterface $stationLoader;
    private ActionControllerContext&MockInterface $context;
    private StarmapRelations $relations;
    private StarmapSensorData $sensorData;
    private StarmapSensorCoverage $sensorCoverage;
    private ScanUserStarmap $subject;
    private User&MockInterface $user;
    private Layer&MockInterface $layer;

    #[\Override]
    protected function setUp(): void
    {
        $layerRepository = $this->mock(LayerRepositoryInterface::class);
        $this->spacecraftRepository = $this->mock(SpacecraftRepositoryInterface::class);
        $this->mapRepository = $this->mock(MapRepositoryInterface::class);
        $this->stationLoader = $this->mock(StationLoaderInterface::class);
        $this->context = $this->mock(ActionControllerContext::class);
        $this->user = $this->mock(User::class);
        $this->layer = $this->mock(Layer::class);
        $contacts = $this->mock(ContactRepositoryInterface::class);
        $relations = $this->mock(RelationRepositoryInterface::class);
        $this->relations = new StarmapRelations($relations, $contacts);
        $this->sensorData = new StarmapSensorData(new Parser(), $this->relations);
        $this->sensorCoverage = new StarmapSensorCoverage($this->mapRepository);
        $this->subject = new ScanUserStarmap($layerRepository, $this->spacecraftRepository, $this->stationLoader, $this->sensorData, $this->sensorCoverage, $this->relations);

        $this->context->shouldReceive('getUser')->andReturn($this->user);
        $this->user->shouldReceive('getId')->andReturn(1);
        $this->user->shouldReceive('getAlliance')->andReturn(null);
        $this->user->shouldReceive('getUserLayers')->andReturn(new ArrayCollection([2 => null]));
        $layerRepository->shouldReceive('find')->with(2)->andReturn($this->layer);
        $this->layer->shouldReceive('getId')->andReturn(2);
        $this->layer->shouldReceive('getWidth')->andReturn(10);
        $this->layer->shouldReceive('getHeight')->andReturn(10);
        $contacts->shouldReceive('getOrderedByUser')->with($this->user)->andReturn([]);
        $contacts->shouldReceive('getByRecipient')->with($this->user)->andReturn([]);
        $relations->shouldReceive('getByUserAndAlliance')->with($this->user, null)->andReturn([]);
        request::setMockVars(['layerid' => 2, 'sourceid' => 42, 'x' => 5, 'y' => 5]);
    }

    public function testScanRequiresSessionCheck(): void
    {
        self::assertTrue($this->subject->performSessionCheck());
    }

    public function testUnknownSourceCannotRevealContactsOrSpendEnergy(): void
    {
        $this->spacecraftRepository->shouldReceive('getUserStarmapRealtimeSensorRanges')->with(1, 2)->once()->andReturn([]);
        $result = $this->scan();
        self::assertFalse($result->success);
        self::assertSame('Sensorquelle nicht verfügbar', $result->message);
    }

    public function testSourceIsRevalidatedAfterAcquiringItsLock(): void
    {
        $this->spacecraftRepository->shouldReceive('getUserStarmapRealtimeSensorRanges')->with(1, 2)->twice()->andReturn([$this->range()], []);
        $this->prepareSource(30);
        $result = $this->scan();
        self::assertFalse($result->success);
        self::assertSame('Feld liegt außerhalb des Sensorbereichs', $result->message);
    }

    public function testInsufficientEnergyDoesNotReturnContacts(): void
    {
        $this->prepareRanges();
        $this->prepareSource(14);
        $result = $this->scan();
        self::assertFalse($result->success);
        self::assertSame(14, $result->eps);
        self::assertObjectNotHasProperty('spacecrafts', $result);
    }

    public function testBlockedFieldsCannotBeScanned(): void
    {
        $this->prepareRanges([['x' => 5, 'y' => 5, 'effects' => 'LSS_BLOCKADE']]);
        $this->prepareSource(30);
        $result = $this->scan();
        self::assertFalse($result->success);
        self::assertSame('Feld liegt außerhalb des Sensorbereichs', $result->message);
    }

    public function testFieldsOutsideTheSelectedSourceRangeCannotBeScanned(): void
    {
        $this->prepareRanges();
        $this->prepareSource(30);
        request::setMockVars(['layerid' => 2, 'sourceid' => 42, 'x' => 8, 'y' => 5]);
        self::assertFalse($this->scan()->success);
    }

    public function testSuccessfulScanChargesFifteenEpsAndRevealsOnlyForeignContactsOnTheRequestedField(): void
    {
        $this->prepareRanges();
        $eps = $this->prepareSource(15);
        $eps->shouldReceive('lowerEps')->with(15)->once()->andReturnSelf();
        $eps->shouldReceive('update')->once();
        $this->spacecraftRepository->shouldReceive('getUserStarmapRealtimeSpacecrafts')->with(1, 2)->once()->andReturn([
            $this->contact(100, 7),
            $this->contact(101, 1),
            $this->contact(102, 8, 6),
            $this->contact(103, 9, 5, true)
        ]);
        $result = $this->scan();
        self::assertTrue($result->success);
        self::assertSame(42, $result->sourceId);
        self::assertCount(1, $result->spacecrafts);
        self::assertSame(100, $result->spacecrafts[0]->id);
        self::assertSame('Schiff 100', $result->spacecrafts[0]->name);
        self::assertSame(7, $result->spacecrafts[0]->userId);
        self::assertObjectNotHasProperty('hull', $result->spacecrafts[0]);
    }

    public function testForeignLiveContactsStayAnonymousUntilScanning(): void
    {
        $this->mapRepository->shouldReceive('getLssBlockadeLocations')->andReturn([]);
        $coverage = $this->sensorCoverage->buildSensorCoverage($this->layer, [$this->range()]);
        $relations = $this->relations->getRelationContext($this->user);
        $relations['enemyUserIds'][7] = true;
        $live = $this->sensorData->normalizeVisibleSpacecraft($this->contact(100, 7), $this->user, false, $relations, $coverage);
        $scanned = $this->sensorData->normalizeVisibleSpacecraft($this->contact(100, 7), $this->user, false, $relations, $coverage, true);
        self::assertSame('Unbekannte Signatur', $live['name']);
        self::assertSame(0, $live['userId']);
        self::assertSame(0, $live['rumpId']);
        self::assertFalse($live['isEnemy']);
        self::assertTrue($scanned['isEnemy']);
        self::assertSame('Schiff 100', $scanned['name']);
        self::assertSame(7, $scanned['userId']);
    }

    public function testOnlyTheSelectedSourcePaysWhenMultipleSourcesCoverTheField(): void
    {
        $secondRange = $this->range();
        $secondRange['source_id'] = 43;
        $this->spacecraftRepository->shouldReceive('getUserStarmapRealtimeSensorRanges')->with(1, 2)->twice()->andReturn([$this->range(), $secondRange]);
        $this->mapRepository->shouldReceive('getLssBlockadeLocations')->andReturn([]);
        request::setMockVars(['layerid' => 2, 'sourceid' => 43, 'x' => 5, 'y' => 5]);
        $eps = $this->prepareSource(30, 43);
        $eps->shouldReceive('lowerEps')->with(15)->once()->andReturnSelf();
        $eps->shouldReceive('update')->once();
        $this->spacecraftRepository->shouldReceive('getUserStarmapRealtimeSpacecrafts')->with(1, 2)->once()->andReturn([]);
        $result = $this->scan();
        self::assertTrue($result->success);
        self::assertSame(43, $result->sourceId);
        self::assertSame([], $result->spacecrafts);
    }

    public function testTachyonContactsStayAnonymousEvenAfterScanning(): void
    {
        $this->mapRepository->shouldReceive('getLssBlockadeLocations')->andReturn([]);
        $range = $this->range();
        $range['tachyon_range'] = 1;
        $coverage = $this->sensorCoverage->buildSensorCoverage($this->layer, [$range]);
        $relations = $this->relations->getRelationContext($this->user);
        $scanned = $this->sensorData->normalizeVisibleSpacecraft($this->contact(103, 7, 5, true), $this->user, false, $relations, $coverage, true);
        self::assertSame('?', $scanned['name']);
        self::assertSame(0, $scanned['userId']);
        self::assertFalse($scanned['isEnemy']);
    }

    public function testPassiveFeedsConcealFieldContactsButAuthorizedScansStillWork(): void
    {
        $this->mapRepository->shouldReceive('getLssBlockadeLocations')->andReturn([]);
        $coverage = $this->sensorCoverage->buildSensorCoverage($this->layer, [$this->range()]);
        $relations = $this->relations->getRelationContext($this->user);
        foreach (['NO_SPACECRAFT_COUNT', 'DUBIOUS_SPACECRAFT_COUNT'] as $effect) {
            $row = $this->contact(100, 7) + ['field_effects' => '["' . $effect . '"]'];
            $passive = $this->sensorData->normalizeVisibleSpacecraft($row, $this->user, false, $relations, $coverage);
            if ($effect === 'NO_SPACECRAFT_COUNT') {
                self::assertNull($passive);
            } else {
                self::assertSame(['isDubiousSignature' => true, 'x' => 5, 'y' => 5], $passive);
            }
            $own = $row;
            $own['user_id'] = 1;
            self::assertSame(100, $this->sensorData->normalizeVisibleSpacecraft($own, $this->user, false, $relations, $coverage)['id']);
            self::assertSame('Schiff 100', $this->sensorData->normalizeVisibleSpacecraft($row, $this->user, false, $relations, $coverage, true)['name']);
        }
    }

    private function prepareRanges(array $blockers = []): void
    {
        $this->spacecraftRepository->shouldReceive('getUserStarmapRealtimeSensorRanges')->with(1, 2)->twice()->andReturn([$this->range()]);
        $this->mapRepository->shouldReceive('getLssBlockadeLocations')->andReturn($blockers);
    }

    private function prepareSource(int $energy, int $sourceId = 42): EpsSystemData&MockInterface
    {
        $wrapper = $this->mock(StationWrapperInterface::class);
        $eps = $this->mock(EpsSystemData::class);
        $this->stationLoader->shouldReceive('getWrapperByIdAndUser')->with($sourceId, 1, true)->once()->andReturn($wrapper);
        $wrapper->shouldReceive('getEpsSystemData')->andReturn($eps);
        $eps->shouldReceive('getEps')->andReturn($energy);
        return $eps;
    }

    private function scan(): stdClass
    {
        return json_decode($this->getMethod($this->subject, 'scan')->invoke($this->subject, $this->context), false, 512, JSON_THROW_ON_ERROR);
    }

    private function range(): array
    {
        return ['source_id' => 42, 'x' => 4, 'y' => 5, 'sensor_range' => 2, 'tachyon_range' => 0];
    }

    private function contact(int $id, int $owner, int $x = 5, bool $cloaked = false): array
    {
        return [
            'id' => $id, 'user_id' => $owner, 'alliance_id' => null, 'is_cloaked' => $cloaked, 'alert_state' => 1,
            'name' => 'Schiff ' . $id, 'type' => 'SHIP', 'user_name' => 'Spieler ' . $owner, 'alliance_name' => null,
            'rump_id' => 34, 'rump_name' => 'Rumpf', 'x' => $x, 'y' => 5, 'in_system' => false, 'system_name' => null,
            'hull' => 100, 'max_hull' => 100, 'shield' => 100, 'max_shield' => 100, 'eps' => 50, 'max_eps' => 50,
            'warpdrive' => 50, 'max_warpdrive' => 50
        ];
    }
}
