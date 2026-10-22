<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register;

use Doctrine\ORM\EntityManagerInterface;
use Noodlehaus\ConfigInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\ErrorHandling\ErrorCodeEnum;
use Stu\Component\Player\Register\Exception\RegistrationException;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\StuHashInterface;
use Stu\Module\Index\Action\Register\Register;
use Stu\Module\Index\Action\Register\RegisterRequestInterface;
use Stu\Module\Index\View\ShowFinishRegistration\ShowFinishRegistration;
use Stu\Orm\Entity\BlockedUser;
use Stu\Orm\Entity\Faction;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserRegistration;
use Stu\Orm\Repository\BlockedUserRepositoryInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\Orm\Repository\UserRefererRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

final class BlockedRegistrationTest extends StuTestCase
{
    public static function creationPaths(): iterable
    {
        foreach (['ordinary', 'sms', 'local', 'local-sms', 'register', 'ordinary-no-mobile', 'register-no-mobile'] as $path) {
            foreach (['email', 'mobile', 'none'] as $blockedIdentifier) {
                if ($blockedIdentifier === 'mobile' && str_ends_with($path, 'no-mobile')) {
                    continue;
                }
                yield $path . '-' . $blockedIdentifier => [$path, $blockedIdentifier];
            }
        }
    }

    #[DataProvider('creationPaths')]
    public function testEveryCreationPathChecksBlockedIdentifiersBeforePersistence(string $path, string $blockedIdentifier): void
    {
        $hasMobile = !str_ends_with($path, 'no-mobile');
        $mobile = $hasMobile ? '+49 1234567890' : null;
        $normalizedMobile = $hasMobile ? '00491234567890' : null;
        $allowed = $blockedIdentifier === 'none';
        $isLocal = $path === 'local';
        $isSms = in_array($path, ['sms', 'local-sms', 'register'], true);
        $users = $this->mock(UserRepositoryInterface::class);
        $defaults = $this->mock(PlayerDefaultsCreatorInterface::class);
        $emails = $this->mock(RegistrationEmailSenderInterface::class);
        $sms = $this->mock(SmsVerificationCodeSenderInterface::class);
        $hasher = $this->mock(StuHashInterface::class);
        $hasher->shouldReceive('hash')->andReturnUsing(fn (string $input): string => sha1($input));
        $em = $this->mock(EntityManagerInterface::class);
        $blocked = $this->mock(BlockedUserRepositoryInterface::class);
        $blocked->shouldReceive('getByEmailHash')->with(sha1('test@example.test'))->once()->andReturn($blockedIdentifier === 'email' ? new BlockedUser() : null);
        if ($blockedIdentifier !== 'email' && $hasMobile) {
            $blocked->shouldReceive('getByMobileHash')->with(sha1('00491234567890'))->once()->andReturn($blockedIdentifier === 'mobile' ? new BlockedUser() : null);
        } else {
            $blocked->shouldNotReceive('getByMobileHash');
        }
        if (!$isLocal) {
            $users->shouldReceive('getByLogin')->with('testuser')->once()->andReturnNull();
            $users->shouldReceive('getByEmail')->with('test@example.test')->once()->andReturnNull();
            if ($hasMobile) {
                $users->shouldReceive('getByMobile')->with('00491234567890', sha1('00491234567890'))->once()->andReturnNull();
            }
        }
        $creatorClass = str_starts_with($path, 'local') ? LocalPlayerCreator::class : PlayerCreator::class;
        $creator = new $creatorClass($users, $defaults, $emails, $sms, $hasher, $em, $this->mock(UserRefererRepositoryInterface::class), $blocked);
        $faction = $this->mock(Faction::class);
        $registration = null;
        if ($allowed) {
            $user = $this->mock(User::class);
            $registration = new UserRegistration($user);
            $user->shouldReceive('getId')->andReturn(101);
            $user->shouldReceive('getRegistration')->andReturn($registration);
            $user->shouldReceive('setFaction')->with($faction)->once()->andReturnSelf();
            $user->shouldReceive('setUsername')->with('Siedler 101')->once()->andReturnSelf();
            if (!$isLocal) {
                $user->shouldReceive('setState')->andReturnSelf();
            }
            $users->shouldReceive('prototype')->once()->andReturn($user);
            $users->shouldReceive('save')->with($user)->twice();
            $em->shouldReceive('flush')->once();
            $defaults->shouldReceive('createDefault')->with($user)->once();
            if ($isSms) {
                $sms->shouldReceive('send')->with($user, \Mockery::type('string'))->once();
                $emails->shouldReceive('send')->with($user, \Mockery::type('string'))->once();
            } else {
                $sms->shouldNotReceive('send');
                $emails->shouldNotReceive('send');
            }
        } else {
            $users->shouldNotReceive('prototype');
            $users->shouldNotReceive('save');
            $em->shouldNotReceive('flush');
            $defaults->shouldNotReceive('createDefault');
            $sms->shouldNotReceive('send');
            $emails->shouldNotReceive('send');
            $this->expectException(RegistrationException::class);
            $this->expectExceptionMessage(ErrorCodeEnum::REGISTRATION_BLOCKED->getDescription());
        }
        if (str_starts_with($path, 'register')) {
            $this->register($creator, $faction, $hasMobile, $allowed);
        } elseif ($isSms) {
            $creator->createWithMobileNumber('testuser', '  TEST@EXAMPLE.TEST  ', $faction, $mobile, 'Allowed123!');
        } else {
            $creator->createPlayer('testuser', '  TEST@EXAMPLE.TEST  ', $faction, 'Allowed123!', $mobile);
        }
        self::assertNotNull($registration);
        self::assertSame('test@example.test', $registration->getEmail());
        self::assertSame($isLocal ? null : $normalizedMobile, $registration->getMobile());
        self::assertTrue(password_verify('Allowed123!', $registration->getPassword()));
        if ($isSms) {
            self::assertNotNull($registration->getSmsCode());
            self::assertNotNull($registration->getEmailCode());
        }
    }

    private function register(PlayerCreatorInterface $creator, Faction $faction, bool $hasMobile, bool $allowed): void
    {
        $request = $this->mock(RegisterRequestInterface::class);
        $request->shouldReceive('getFactionId')->andReturn(1);
        $request->shouldReceive('getLoginName')->andReturn('testuser');
        $request->shouldReceive('getEmailAddress')->andReturn(' TEST@EXAMPLE.TEST ');
        $request->shouldReceive('getCountryCode')->andReturn($hasMobile ? '+49' : '');
        $request->shouldReceive('getMobileNumber')->andReturn($hasMobile ? '1234567890' : '');
        $request->shouldReceive('getPassword')->andReturn('Allowed123!');
        $request->shouldReceive('getPasswordReEntered')->andReturn('Allowed123!');
        $request->shouldReceive('getReferer')->andReturnNull();
        $faction->shouldReceive('getPlayerLimit')->andReturn(0);
        $factions = $this->mock(FactionRepositoryInterface::class);
        $factions->shouldReceive('getPlayableFactionsPlayerCount')->andReturn([1 => ['faction' => $faction, 'count' => 0]]);
        $config = $this->mock(ConfigInterface::class);
        $config->shouldReceive('get')->with('game.registration.enabled')->andReturnTrue();
        $config->shouldReceive('get')->with('game.registration.sms_code_verification.enabled')->andReturnFalse();
        $referrals = $this->mock(RegistrationReferralTrackerInterface::class);
        $referrals->shouldReceive('prependStoredReferralCode')->with(null)->andReturnNull();
        $context = $this->mock(ActionControllerContext::class);
        if ($allowed) {
            $referrals->shouldReceive('clearStoredReferralCode')->once();
            $context->shouldReceive('setView')->with(ShowFinishRegistration::VIEW_IDENTIFIER)->once();
        } else {
            $context->shouldNotReceive('setView');
        }
        new Register($request, $factions, $creator, $config, $referrals)->handle($context);
    }
}
