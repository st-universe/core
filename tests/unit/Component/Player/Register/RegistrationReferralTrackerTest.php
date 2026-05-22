<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register;

use Mockery\MockInterface;
use request;
use Stu\Orm\Entity\RegistrationReferralCode;
use Stu\Orm\Repository\RegistrationReferralCodeRepositoryInterface;
use Stu\StuTestCase;

class RegistrationReferralTrackerTest extends StuTestCase
{
    private MockInterface&RegistrationReferralCodeRepositoryInterface $registrationReferralCodeRepository;

    private RegistrationReferralTracker $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->registrationReferralCodeRepository = $this->mock(RegistrationReferralCodeRepositoryInterface::class);
        $this->subject = new RegistrationReferralTracker($this->registrationReferralCodeRepository);

        $_COOKIE = [];
        $_SESSION = [];
        $_SERVER['REQUEST_URI'] = '/';
        unset($_SERVER['HTTPS'], $_SERVER['HTTP_X_FORWARDED_PROTO']);
    }

    #[\Override]
    protected function tearDown(): void
    {
        $_COOKIE = [];
        $_SESSION = [];
        unset($_SERVER['REQUEST_URI'], $_SERVER['HTTPS'], $_SERVER['HTTP_X_FORWARDED_PROTO']);

        parent::tearDown();
    }

    public function testCaptureFromRequestStoresValidReferralCodeAndReturnsCleanRedirectTarget(): void
    {
        $referralCode = $this->mock(RegistrationReferralCode::class);

        request::setMockVars([
            'ref' => 'YT',
            'SHOW_REGISTRATION' => '1'
        ]);
        $_SERVER['REQUEST_URI'] = '/index.php?ref=YT&SHOW_REGISTRATION=1';

        $this->registrationReferralCodeRepository->shouldReceive('getActiveByCode')
            ->with('yt')
            ->once()
            ->andReturn($referralCode);
        $this->registrationReferralCodeRepository->shouldReceive('incrementHitCount')
            ->with($referralCode)
            ->once();

        $referralCode->shouldReceive('getCode')
            ->withNoArgs()
            ->once()
            ->andReturn('yt');

        static::assertSame('/index.php?SHOW_REGISTRATION=1', $this->subject->captureFromRequest());
        static::assertSame('yt', $_SESSION['stu_registration_ref']);
        static::assertSame('yt', $_COOKIE['stu_registration_ref']);
    }

    public function testCaptureFromRequestIgnoresUnknownReferralCodeButStillRedirects(): void
    {
        request::setMockVars(['ref' => 'unknown']);
        $_SERVER['REQUEST_URI'] = '/?ref=unknown';

        $this->registrationReferralCodeRepository->shouldReceive('getActiveByCode')
            ->with('unknown')
            ->once()
            ->andReturnNull();
        $this->registrationReferralCodeRepository->shouldReceive('incrementHitCount')
            ->never();

        static::assertSame('/', $this->subject->captureFromRequest());
        static::assertArrayNotHasKey('stu_registration_ref', $_SESSION);
        static::assertArrayNotHasKey('stu_registration_ref', $_COOKIE);
    }

    public function testPrependStoredReferralCodePrependsCodeToSubmittedText(): void
    {
        $referralCode = $this->mock(RegistrationReferralCode::class);

        $_SESSION['stu_registration_ref'] = 'yt';

        $this->registrationReferralCodeRepository->shouldReceive('getActiveByCode')
            ->with('yt')
            ->once()
            ->andReturn($referralCode);

        $referralCode->shouldReceive('getCode')
            ->withNoArgs()
            ->once()
            ->andReturn('yt');

        static::assertSame('yt Discord', $this->subject->prependStoredReferralCode(' Discord '));
    }

    public function testClearStoredReferralCodeRemovesSessionAndCookieValue(): void
    {
        $_SESSION['stu_registration_ref'] = 'yt';
        $_COOKIE['stu_registration_ref'] = 'yt';

        $this->subject->clearStoredReferralCode();

        static::assertArrayNotHasKey('stu_registration_ref', $_SESSION);
        static::assertArrayNotHasKey('stu_registration_ref', $_COOKIE);
    }
}
