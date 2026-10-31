<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib;

use Mockery\MockInterface;
use Stu\Lib\Session\SessionStorageInterface;
use Stu\Module\Control\StuTime;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

final class BorgAssimilationTest extends StuTestCase
{
    private const string STORAGE_KEY = 'anniversary_25_borg_started_at';
    private const string DISABLED_KEY = 'anniversary_25_borg_disabled';

    private SessionStorageInterface&MockInterface $storage;
    private StuTime&MockInterface $time;
    private BorgAssimilation $subject;
    private mixed $previousSession;

    #[\Override]
    protected function setUp(): void
    {
        $this->previousSession = $_SESSION ?? null;
        $_SESSION = [];
        $this->storage = $this->mock(SessionStorageInterface::class);
        $this->storage->shouldReceive('getSessionValue')->with(self::DISABLED_KEY)->andReturn(false)->byDefault();
        $this->time = $this->mock(StuTime::class);
        $this->subject = new BorgAssimilation($this->storage, $this->time);
    }

    #[\Override]
    protected function tearDown(): void
    {
        if ($this->previousSession === null) {
            unset($_SESSION);
        } else {
            $_SESSION = $this->previousSession;
        }
        parent::tearDown();
    }

    public function testGuestStartsWithOriginalInterfaceAndContinuesAcrossPages(): void
    {
        $this->time->shouldReceive('time')->twice()->andReturn(1000, 1012);

        self::assertSame(0, $this->subject->getElapsedMilliseconds(null));
        self::assertSame(12_000, $this->subject->getElapsedMilliseconds(null));
        self::assertSame(1000, $_SESSION[self::STORAGE_KEY]);
    }

    public function testLoginPersistsGuestProgressInAccount(): void
    {
        $_SESSION[self::STORAGE_KEY] = 960;
        $this->time->shouldReceive('time')->once()->andReturn(1000);
        $this->storage->shouldReceive('getSessionValue')->with(self::STORAGE_KEY)->once()->andReturn(false);
        $this->storage->shouldReceive('storeSessionData')->with(self::STORAGE_KEY, 960, true)->once();

        self::assertSame(40_000, $this->subject->getElapsedMilliseconds(new User()));
        self::assertArrayNotHasKey(self::STORAGE_KEY, $_SESSION);
    }

    public function testExistingAccountResumesSavedProgressOnAnotherDevice(): void
    {
        $this->time->shouldReceive('time')->once()->andReturn(1000);
        $this->storage->shouldReceive('getSessionValue')->with(self::STORAGE_KEY)->once()->andReturn(900);
        $this->storage->shouldNotReceive('storeSessionData');

        self::assertSame(100_000, $this->subject->getElapsedMilliseconds(new User()));
    }

    public function testFurtherAdvancedGuestProgressAlsoUpdatesAnExistingAccount(): void
    {
        $_SESSION[self::STORAGE_KEY] = 920;
        $this->time->shouldReceive('time')->once()->andReturn(1000);
        $this->storage->shouldReceive('getSessionValue')->with(self::STORAGE_KEY)->once()->andReturn(980);
        $this->storage->shouldReceive('deleteSessionData')->with(self::STORAGE_KEY)->once();
        $this->storage->shouldReceive('storeSessionData')->with(self::STORAGE_KEY, 920, true)->once();

        self::assertSame(80_000, $this->subject->getElapsedMilliseconds(new User()));
    }

    public function testGuestCannotResetAnAssimilatedAccount(): void
    {
        $_SESSION[self::STORAGE_KEY] = 990;
        $this->time->shouldReceive('time')->once()->andReturn(1000);
        $this->storage->shouldReceive('getSessionValue')->with(self::STORAGE_KEY)->once()->andReturn(700);
        $this->storage->shouldNotReceive('storeSessionData');

        self::assertSame(BorgAssimilation::DURATION_MILLISECONDS, $this->subject->getElapsedMilliseconds(new User()));
    }

    public function testNewAccountStartsIndependentlyAfterPreviousAccountConsumedGuestState(): void
    {
        $this->time->shouldReceive('time')->twice()->andReturn(1000, 1010);
        $this->storage->shouldReceive('getSessionValue')->with(self::STORAGE_KEY)->twice()->andReturn(700, false);
        $this->storage->shouldReceive('storeSessionData')->with(self::STORAGE_KEY, 1010, true)->once();

        self::assertSame(150_000, $this->subject->getElapsedMilliseconds(new User()));
        self::assertSame(0, $this->subject->getElapsedMilliseconds(new User()));
    }

    public function testInvalidAccountTimestampIsRepaired(): void
    {
        $this->time->shouldReceive('time')->once()->andReturn(1000);
        $this->storage->shouldReceive('getSessionValue')->with(self::STORAGE_KEY)->once()->andReturn(2000);
        $this->storage->shouldReceive('deleteSessionData')->with(self::STORAGE_KEY)->once();
        $this->storage->shouldReceive('storeSessionData')->with(self::STORAGE_KEY, 1000, true)->once();

        self::assertSame(0, $this->subject->getElapsedMilliseconds(new User()));
    }

    public function testInvalidGuestStateCannotProduceNegativeProgress(): void
    {
        $_SESSION[self::STORAGE_KEY] = ['invalid'];
        $this->time->shouldReceive('time')->once()->andReturn(1000);

        self::assertSame(0, $this->subject->getElapsedMilliseconds(null));
    }

    public function testRestartReplacesGuestTimeline(): void
    {
        $_SESSION[self::STORAGE_KEY] = 700;
        $this->time->shouldReceive('time')->twice()->andReturn(1000);

        $this->subject->restart(null);

        self::assertSame(1000, $_SESSION[self::STORAGE_KEY]);
        self::assertSame(0, $this->subject->getElapsedMilliseconds(null));
        self::assertSame(1000, $this->subject->getStartedAt());
    }

    public function testRestartReplacesAccountTimelineAndClearsOldGuestProgress(): void
    {
        $_SESSION[self::STORAGE_KEY] = 700;
        $this->time->shouldReceive('time')->once()->andReturn(1000);
        $this->storage->shouldReceive('deleteSessionData')->with(self::STORAGE_KEY)->once();
        $this->storage->shouldReceive('storeSessionData')->with(self::STORAGE_KEY, 1000, true)->once();

        $this->subject->restart(new User());

        self::assertArrayNotHasKey(self::STORAGE_KEY, $_SESSION);
        self::assertSame(1000, $this->subject->getStartedAt());
    }

    public function testDisabledAccountIgnoresGuestProgressAndNeverAdvances(): void
    {
        $_SESSION[self::STORAGE_KEY] = 700;
        $user = new User();
        $this->storage->shouldReceive('getSessionValue')->with(self::DISABLED_KEY)->andReturn(true);
        $this->storage->shouldNotReceive('getSessionValue')->with(self::STORAGE_KEY);
        $this->storage->shouldNotReceive('storeSessionData');
        $this->time->shouldNotReceive('time');

        self::assertFalse($this->subject->isEnabled($user));
        self::assertSame(0, $this->subject->getElapsedMilliseconds($user));
        self::assertSame(0, $this->subject->getStartedAt());
        self::assertArrayNotHasKey(self::STORAGE_KEY, $_SESSION);
    }

    public function testGuestIsAlwaysEnabledWithoutReadingAccountPreferences(): void
    {
        $this->storage->shouldNotReceive('getSessionValue');

        self::assertTrue($this->subject->isEnabled(null));
    }

    public function testDeassimilationPersistsOptOutAndRemovesGuestAndAccountTimelines(): void
    {
        $_SESSION[self::STORAGE_KEY] = 700;
        $this->storage->shouldReceive('deleteSessionData')->with(self::STORAGE_KEY)->once();
        $this->storage->shouldReceive('deleteSessionData')->with(self::DISABLED_KEY)->once();
        $this->storage->shouldReceive('storeSessionData')->with(self::DISABLED_KEY, true, true)->once();
        $this->time->shouldNotReceive('time');

        $this->subject->deassimilate(new User());

        self::assertSame(0, $this->subject->getStartedAt());
        self::assertArrayNotHasKey(self::STORAGE_KEY, $_SESSION);
    }

    public function testReassimilationEnablesTheAccountAndStartsFromZero(): void
    {
        $_SESSION[self::STORAGE_KEY] = 700;
        $this->storage->shouldReceive('deleteSessionData')->with(self::DISABLED_KEY)->once();
        $this->storage->shouldReceive('deleteSessionData')->with(self::STORAGE_KEY)->once();
        $this->storage->shouldReceive('storeSessionData')->with(self::STORAGE_KEY, 1000, true)->once();
        $this->time->shouldReceive('time')->once()->andReturn(1000);

        $this->subject->reassimilate(new User());

        self::assertSame(1000, $this->subject->getStartedAt());
        self::assertArrayNotHasKey(self::STORAGE_KEY, $_SESSION);
    }

}
