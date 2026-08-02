<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer\Strategy;

use Mockery\MockInterface;
use request;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Transfer\EntityWithStorageInterface;
use Stu\Lib\Transfer\Wrapper\StorageEntityWrapperInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\TorpedoStorage;
use Stu\Orm\Entity\TorpedoType;
use Stu\StuTestCase;

class TorpedoTransferStrategyTest extends StuTestCase
{
    private MockInterface&StorageEntityWrapperInterface $source;
    private MockInterface&StorageEntityWrapperInterface $target;
    private MockInterface&InformationInterface $information;

    private TorpedoTransferStrategy $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->source = $this->mock(StorageEntityWrapperInterface::class);
        $this->target = $this->mock(StorageEntityWrapperInterface::class);
        $this->information = $this->mock(InformationInterface::class);
        $this->subject = new TorpedoTransferStrategy();
    }

    public function testProvidesEveryCompatibleLoadedTorpedoType(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $targetEntity = $this->mock(EntityWithStorageInterface::class);
        [$firstStorage, $firstTorpedo] = $this->createTorpedoStorage(1, 20);
        [$secondStorage, $secondTorpedo] = $this->createTorpedoStorage(2, 30);

        $this->source->shouldReceive('getTorpedoStorages')
            ->withNoArgs()
            ->once()
            ->andReturn([$firstStorage, $secondStorage]);
        $this->target->shouldReceive('getMaxTorpedos')->withNoArgs()->once()->andReturn(100);
        $this->target->shouldReceive('getTotalTorpedoCount')->withNoArgs()->once()->andReturn(40);
        $this->target->shouldReceive('get')->withNoArgs()->twice()->andReturn($targetEntity);
        $this->target->shouldReceive('canStoreTorpedoType')->with($firstTorpedo)->once()->andReturn(true);
        $this->target->shouldReceive('canStoreTorpedoType')->with($secondTorpedo)->once()->andReturn(true);
        $game->shouldReceive('setTemplateVar')
            ->with('TORPEDO_TRANSFERS', [
                ['torpedo' => $firstTorpedo, 'maximum' => 20],
                ['torpedo' => $secondTorpedo, 'maximum' => 30]
            ])
            ->once();

        $this->subject->setTemplateVariables(true, $this->source, $this->target, $game);
    }

    public function testTransfersMultipleTypesWithoutExceedingTotalCapacity(): void
    {
        request::setMockVars(['tcount' => [1 => 60, 2 => 60]]);

        [$firstStorage, $firstTorpedo] = $this->createTorpedoStorage(1, 60);
        [$secondStorage, $secondTorpedo] = $this->createTorpedoStorage(2, 60);
        $location = $this->mock(Location::class);

        $this->source->shouldReceive('canTransferTorpedos')->with($this->information)->once()->andReturn(true);
        $this->source->shouldReceive('getTorpedoStorages')
            ->withNoArgs()
            ->once()
            ->andReturn([$firstStorage, $secondStorage]);
        $this->target->shouldReceive('canStoreTorpedoType')
            ->with($firstTorpedo, $this->information)
            ->once()
            ->andReturn(true);
        $this->target->shouldReceive('canStoreTorpedoType')
            ->with($secondTorpedo, $this->information)
            ->once()
            ->andReturn(true);
        $this->target->shouldReceive('getMaxTorpedos')->withNoArgs()->twice()->andReturn(100);
        $this->target->shouldReceive('getTotalTorpedoCount')->withNoArgs()->twice()->andReturn(0, 60);
        $this->target->shouldReceive('changeTorpedo')->with(60, $firstTorpedo)->once();
        $this->target->shouldReceive('changeTorpedo')->with(40, $secondTorpedo)->once();
        $this->source->shouldReceive('changeTorpedo')->with(-60, $firstTorpedo)->once();
        $this->source->shouldReceive('changeTorpedo')->with(-40, $secondTorpedo)->once();
        $this->source->shouldReceive('getName')->withNoArgs()->twice()->andReturn('source');
        $this->source->shouldReceive('getLocation')->withNoArgs()->twice()->andReturn($location);
        $location->shouldReceive('getSectorString')->withNoArgs()->twice()->andReturn('1|2');
        $this->target->shouldReceive('getName')->withNoArgs()->twice()->andReturn('target');
        $this->information->shouldReceive('addInformation')
            ->with('Die source hat in Sektor 1|2 60 Torpedo 1 zur target transferiert')
            ->once();
        $this->information->shouldReceive('addInformation')
            ->with('Die source hat in Sektor 1|2 40 Torpedo 2 zur target transferiert')
            ->once();

        $this->subject->transfer(true, $this->source, $this->target, $this->information);
    }

    public function testShowsCompatibleTypesWhenDestinationIsFull(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $targetEntity = $this->mock(EntityWithStorageInterface::class);
        [$firstStorage, $firstTorpedo] = $this->createTorpedoStorage(1, 20);
        [$secondStorage, $secondTorpedo] = $this->createTorpedoStorage(2, 30);

        $this->source->shouldReceive('getTorpedoStorages')->withNoArgs()->once()->andReturn([$firstStorage, $secondStorage]);
        $this->target->shouldReceive('getMaxTorpedos')->withNoArgs()->once()->andReturn(100);
        $this->target->shouldReceive('getTotalTorpedoCount')->withNoArgs()->once()->andReturn(100);
        $this->target->shouldReceive('get')->withNoArgs()->twice()->andReturn($targetEntity);
        $this->target->shouldReceive('canStoreTorpedoType')->with($firstTorpedo)->once()->andReturn(true);
        $this->target->shouldReceive('canStoreTorpedoType')->with($secondTorpedo)->once()->andReturn(true);
        $game->shouldReceive('setTemplateVar')
            ->with('TORPEDO_TRANSFERS', [
                ['torpedo' => $firstTorpedo, 'maximum' => 0],
                ['torpedo' => $secondTorpedo, 'maximum' => 0]
            ])
            ->once();

        $this->subject->setTemplateVariables(true, $this->source, $this->target, $game);
    }

    public function testHidesMismatchingLevelsForDestinationWithoutTransportModule(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $targetEntity = $this->mock(Spacecraft::class);
        $rump = $this->mock(SpacecraftRump::class);
        [$matchingStorage, $matchingTorpedo] = $this->createTorpedoStorage(1, 20);
        [$mismatchingStorage, $mismatchingTorpedo] = $this->createTorpedoStorage(2, 30);

        $matchingTorpedo->shouldReceive('getLevel')->withNoArgs()->once()->andReturn(4);
        $mismatchingTorpedo->shouldReceive('getLevel')->withNoArgs()->once()->andReturn(5);
        $this->source->shouldReceive('getTorpedoStorages')
            ->withNoArgs()
            ->once()
            ->andReturn([$matchingStorage, $mismatchingStorage]);
        $this->target->shouldReceive('getMaxTorpedos')->withNoArgs()->once()->andReturn(100);
        $this->target->shouldReceive('getTotalTorpedoCount')->withNoArgs()->once()->andReturn(0);
        $this->target->shouldReceive('get')->withNoArgs()->twice()->andReturn($targetEntity);
        $this->target->shouldReceive('canStoreTorpedoType')->with($matchingTorpedo)->once()->andReturn(true);
        $this->target->shouldReceive('canStoreTorpedoType')->with($mismatchingTorpedo)->never();
        $targetEntity->shouldReceive('isTorpedoStorageHealthy')->withNoArgs()->twice()->andReturn(false);
        $targetEntity->shouldReceive('getRump')->withNoArgs()->twice()->andReturn($rump);
        $rump->shouldReceive('getTorpedoLevel')->withNoArgs()->twice()->andReturn(4);
        $game->shouldReceive('setTemplateVar')
            ->with('TORPEDO_TRANSFERS', [['torpedo' => $matchingTorpedo, 'maximum' => 20]])
            ->once();

        $this->subject->setTemplateVariables(true, $this->source, $this->target, $game);
    }

    /** @return array{0: MockInterface&TorpedoStorage, 1: MockInterface&TorpedoType} */
    private function createTorpedoStorage(int $id, int $amount): array
    {
        $torpedoStorage = $this->mock(TorpedoStorage::class);
        $torpedoType = $this->mock(TorpedoType::class);
        $torpedoStorage->shouldReceive('getTorpedo')->withNoArgs()->andReturn($torpedoType);
        $torpedoStorage->shouldReceive('getStorage->getAmount')->withNoArgs()->andReturn($amount);
        $torpedoType->shouldReceive('getId')->withNoArgs()->andReturn($id);
        $torpedoType->shouldReceive('getName')->withNoArgs()->andReturn(sprintf('Torpedo %d', $id));

        return [$torpedoStorage, $torpedoType];
    }
}
