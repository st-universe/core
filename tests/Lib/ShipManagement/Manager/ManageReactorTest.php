<?php

declare(strict_types=1);

namespace Stu\Lib\ShipManagement\Manager;

use Doctrine\Common\Collections\Collection;
use Mockery\MockInterface;
use RuntimeException;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\ShipManagement\Provider\ManagerProviderInterface;
use Stu\Module\Ship\Lib\ReactorUtilInterface;
use Stu\Module\Ship\Lib\ReactorWrapperInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\StuTestCase;

class ManageReactorTest extends StuTestCase
{
    /** @var MockInterface&ReactorUtilInterface */
    private MockInterface $reactorUtil;

    /** @var MockInterface&ShipWrapperInterface */
    private MockInterface $wrapper;

    /** @var MockInterface&ShipInterface */
    private MockInterface $ship;

    /** @var MockInterface&ManagerProviderInterface */
    private MockInterface $managerProvider;

    private int $shipId = 555;

    private ManageReactor $subject;

    protected function setUp(): void
    {
        $this->reactorUtil = $this->mock(ReactorUtilInterface::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->ship = $this->mock(ShipInterface::class);
        $this->user = $this->mock(UserInterface::class);
        $this->managerProvider = $this->mock(ManagerProviderInterface::class);

        $this->subject = new ManageReactor(
            $this->reactorUtil
        );
    }

    public function testManageExpectErrorWhenValuesNotPresent(): void
    {
        static::expectExceptionMessage('value array not existent');
        static::expectException(RuntimeException::class);

        $values = ['foo' => '42'];

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEmpty($msg);
    }

    public function testManageExpectNothingWhenNotInValues(): void
    {
        $values = ['reactor' => ['5' => '42']];

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($this->shipId);

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEmpty($msg);
    }

    public function testManageExpectNothingWhenValueIsZero(): void
    {
        $values = ['reactor' => ['555' => '0']];

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->twice()
            ->andReturn($this->shipId);

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEmpty($msg);
    }

    public function testManageExpectNothingWhenNeitherWarpcoreNoreFusionInstalled(): void
    {
        $values = ['reactor' => ['555' => '42']];

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->wrapper->shouldReceive('getReactorWrapper')
            ->withNoArgs()
            ->once()
            ->andReturnNull();

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->twice()
            ->andReturn($this->shipId);

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEmpty($msg);
    }

    public function testManageExpectInfoMessageWhenInsufficientCommoditiesOnProvider(): void
    {
        $reactorWrapper = $this->mock(ReactorWrapperInterface::class);
        $storage = $this->mock(Collection::class);
        $values = ['reactor' => ['555' => '42']];

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->wrapper->shouldReceive('getReactorWrapper')
            ->withNoArgs()
            ->once()
            ->andReturn($reactorWrapper);

        $reactorWrapper->shouldReceive('get->getSystemType')
            ->withNoArgs()
            ->once()
            ->andReturn(ShipSystemTypeEnum::SYSTEM_WARPCORE);
        $reactorWrapper->shouldReceive('get->getLoadCost')
            ->withNoArgs()
            ->once()
            ->andReturn(ReactorWrapperInterface::WARPCORE_LOAD_COST);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->twice()
            ->andReturn($this->shipId);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('name');

        $this->managerProvider->shouldReceive('getStorage')
            ->withNoArgs()
            ->andReturn($storage);

        $this->reactorUtil->shouldReceive('storageContainsNeededCommodities')
            ->with($storage, $reactorWrapper)
            ->andReturn(false);

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEquals([
            'name: Es werden mindestens folgende Waren zum Aufladen des Warpkerns benötigt:',
            '1 Dilithium',
            '2 Antimaterie',
            '2 Deuterium'
        ], $msg);
    }

    public function testManageExpectLoadingWhenValueIsNumeric(): void
    {
        $reactorWrapper = $this->mock(ReactorWrapperInterface::class);
        $storage = $this->mock(Collection::class);
        $values = ['reactor' => ['555' => '42']];

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->wrapper->shouldReceive('getReactorWrapper')
            ->withNoArgs()
            ->once()
            ->andReturn($reactorWrapper);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($this->shipId);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('name');

        $this->managerProvider->shouldReceive('getStorage')
            ->withNoArgs()
            ->andReturn($storage);

        $this->reactorUtil->shouldReceive('storageContainsNeededCommodities')
            ->with($storage, $reactorWrapper)
            ->andReturn(true);
        $this->reactorUtil->shouldReceive('loadReactor')
            ->with($this->ship, 42, $this->managerProvider, $reactorWrapper)
            ->andReturn('LOADED');

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEquals(['LOADED'], $msg);
    }

    public function testManageExpectLoadingWhenValueIsLetterM(): void
    {
        $reactorWrapper = $this->mock(ReactorWrapperInterface::class);
        $storage = $this->mock(Collection::class);
        $values = ['reactor' => ['555' => 'm']];

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->wrapper->shouldReceive('getReactorWrapper')
            ->withNoArgs()
            ->once()
            ->andReturn($reactorWrapper);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($this->shipId);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('name');

        $this->managerProvider->shouldReceive('getStorage')
            ->withNoArgs()
            ->andReturn($storage);

        $this->reactorUtil->shouldReceive('storageContainsNeededCommodities')
            ->with($storage, $reactorWrapper)
            ->andReturn(true);
        $this->reactorUtil->shouldReceive('loadReactor')
            ->with($this->ship, PHP_INT_MAX, $this->managerProvider, $reactorWrapper)
            ->andReturn('LOADED');

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEquals(['LOADED'], $msg);
    }
}
