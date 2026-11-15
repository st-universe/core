<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Spacecraft\System\Data\EpsSystemData;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftAssimilated;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

final class BorgTractorRescueDeadlineTest extends StuTestCase
{
    /** @return iterable<string, array{int, int, bool}> */
    public static function deadlines(): iterable
    {
        yield '1499 seconds for capture beginning at600' => [600, 1499, true];
        yield '1500 seconds for capture beginning at600' => [600, 1500, false];
        yield 'one second before deadline with unaligned start' => [1007, 1906, true];
        yield 'exact deadline with unaligned start' => [1007, 1907, false];
    }

    #[DataProvider('deadlines')]
    public function testTheRescueDeadlineUsesTheExactCaptureStart(int $start, int $now, bool $expected): void
    {
        $cube = $this->cube($start);
        $clock = $this->mock(StuTime::class);
        $clock->shouldReceive('time')->once()->andReturn($now);
        $salvos = $this->salvos($this->mock(StuRandom::class), $clock);
        $information = new InformationWrapper();

        self::assertSame($expected, $salvos->isBeforeDeadline($cube, $information));
        self::assertCount($expected ? 0 : 1, $information->getInformations());
        self::assertTrue($cube->getTractoredShip()->isBeingAssimilated());
    }

    public function testAnExpiredCaptureCannotConsumeEnergyOrFireAnySalvo(): void
    {
        $source = new Ship()->setRump(new SpacecraftRump()->setPhaserVolleys(2));
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $wrapper->shouldReceive('get')->andReturn($source);
        $wrapper->shouldNotReceive('getEpsSystemData');
        $target = $this->mock(ShipWrapperInterface::class);
        $target->shouldReceive('get')->andReturn($this->cube(600));
        $random = $this->mock(StuRandom::class);
        $random->shouldNotReceive('rand');
        $clock = $this->mock(StuTime::class);
        $clock->shouldReceive('time')->once()->andReturn(1500);
        $information = new InformationWrapper();

        self::assertFalse($this->salvos($random, $clock)->fire(new BorgSphereEvent(), [$wrapper], $target, $information));
        self::assertSame(['Die Frist zur Befreiung ist abgelaufen. Der Assimilationsabschluss wird verarbeitet'], $information->getInformations());
    }

    /** @return iterable<string, array{int}> */
    public static function salvoBoundaries(): iterable
    {
        yield 'deadline interrupts the same ship' => [2];
        yield 'deadline prevents the following fleet member' => [1];
    }

    #[DataProvider('salvoBoundaries')]
    public function testCrossingTheDeadlineStopsAllFurtherShotsButReportsTheShotAlreadyFired(int $firstShipVolleys): void
    {
        $source = new Ship()->setName('Retter')->setRump(new SpacecraftRump()->setPhaserVolleys($firstShipVolleys));
        $eps = $this->mock(EpsSystemData::class);
        $eps->shouldReceive('getEps')->once()->andReturn(10);
        $eps->shouldReceive('lowerEps')->once()->with(1)->andReturnSelf();
        $eps->shouldReceive('update')->once();
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $wrapper->shouldReceive('get')->andReturn($source);
        $wrapper->shouldReceive('getEpsSystemData')->twice()->andReturn($eps);
        $second = $this->mock(ShipWrapperInterface::class);
        $second->shouldReceive('get')->andReturn(new Ship()->setRump(new SpacecraftRump()->setPhaserVolleys(2)));
        $second->shouldNotReceive('getEpsSystemData');
        $target = $this->mock(ShipWrapperInterface::class);
        $target->shouldReceive('get')->andReturn($this->cube(600));
        $random = $this->mock(StuRandom::class);
        $random->shouldReceive('rand')->once()->with(1, 100)->andReturn(100);
        $clock = $this->mock(StuTime::class);
        $clock->shouldReceive('time')->twice()->andReturn(1499, 1500);
        $information = new InformationWrapper();
        $event = new BorgSphereEvent()->setConfiguration(new BorgSphereConfiguration()->setSettings(['tractor_rescue_chance_percent' => 5]));

        self::assertTrue($this->salvos($random, $clock)->fire($event, [$wrapper, $second], $target, $information));
        self::assertCount(3, $information->getInformations());
        self::assertSame('Die Frist zur Befreiung ist abgelaufen. Der Assimilationsabschluss wird verarbeitet', $information->getInformations()[2]);
    }

    private function salvos(StuRandom $random, StuTime $clock): BorgTractorRescueSalvos
    {
        $lifecycle = new BorgAssimilationLifecycle(static function (): BorgAssimilationService {
            throw new LogicException('Assimilation must not be changed by an expired or missed rescue');
        });
        $systems = $this->mock(SpacecraftSystemManagerInterface::class);
        $systems->shouldNotReceive('deactivate');
        return new BorgTractorRescueSalvos($lifecycle, $systems, $random, $clock);
    }

    private function cube(int $start): Ship
    {
        $owner = $this->mock(User::class);
        $owner->shouldReceive('getId')->andReturn(19);
        $cube = new Ship()->setName('Kubus')->setUser($owner);
        $target = new Ship();
        new SpacecraftAssimilated()->setSpacecraft($target)->start($cube, $start, 900);
        $cube->setTractoredShip($target);
        $cube->getSystems()->set(SpacecraftSystemTypeEnum::TRACTOR_BEAM->value, new SpacecraftSystem()->setSpacecraft($cube)->setSystemType(SpacecraftSystemTypeEnum::TRACTOR_BEAM)->setStatus(100)->setMode(SpacecraftSystemModeEnum::MODE_ON));
        return $cube;
    }
}
