<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register;

use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Override;
use Stu\Component\Player\Register\Exception\EmailAddressInvalidException;
use Stu\Component\Player\Register\Exception\LoginNameInvalidException;
use Stu\Component\Player\Register\Exception\PlayerDuplicateException;
use Stu\Module\Control\StuHashInterface;
use Stu\Orm\Entity\FactionInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserRegistrationInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\Orm\Repository\UserRefererRepositoryInterface;

class PlayerCreatorTest extends MockeryTestCase
{
    private $userRepository;
    private $playerDefaultsCreator;
    private $registrationEmailSender;
    private $smsVerificationCodeSender;
    private $stuHash;
    private $entityManager;
    private $userRefererRepository;

    private PlayerCreatorInterface $creator;

    #[Override]
    public function setUp(): void
    {
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->playerDefaultsCreator = Mockery::mock(PlayerDefaultsCreatorInterface::class);
        $this->registrationEmailSender = Mockery::mock(RegistrationEmailSenderInterface::class);
        $this->smsVerificationCodeSender = Mockery::mock(SmsVerificationCodeSenderInterface::class);
        $this->stuHash = Mockery::mock(StuHashInterface::class);
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);
        $this->userRefererRepository = Mockery::mock(UserRefererRepositoryInterface::class);

        $this->creator = new PlayerCreator(
            $this->userRepository,
            $this->playerDefaultsCreator,
            $this->registrationEmailSender,
            $this->smsVerificationCodeSender,
            $this->stuHash,
            $this->entityManager,
            $this->userRefererRepository
        );
    }

    public function testCreateThrowsErrorOnInvalidLoginName(): void
    {
        $this->expectException(LoginNameInvalidException::class);

        $this->creator->createWithMobileNumber(
            'meh',
            'lol',
            Mockery::mock(FactionInterface::class),
            'mobile',
            'password'
        );
    }

    public function testCreateThrowsErrorOnInvalidEmail(): void
    {
        $this->expectException(EmailAddressInvalidException::class);

        $this->creator->createWithMobileNumber(
            'mehzomglol',
            'lol',
            Mockery::mock(FactionInterface::class),
            'mobile',
            'password'
        );
    }

    public function testCreateThrowsErrorIfUserNameIsNotUnique(): void
    {
        $this->expectException(PlayerDuplicateException::class);

        $loginname = 'mehzomglol';

        $this->userRepository->shouldReceive('getByLogin')
            ->with($loginname)
            ->once()
            ->andReturn(Mockery::mock(UserInterface::class));

        $this->creator->createWithMobileNumber(
            $loginname,
            'lol@example.com',
            Mockery::mock(FactionInterface::class),
            'mobile',
            'password'
        );
    }

    public function testCreateThrowsErrorEmailIsNotUnique(): void
    {
        $this->expectException(PlayerDuplicateException::class);

        $loginname = 'mehzomglol';
        $email = 'lol@example.com';

        $this->userRepository->shouldReceive('getByLogin')
            ->with($loginname)
            ->once()
            ->andReturnNull();
        $this->userRepository->shouldReceive('getByEmail')
            ->with($email)
            ->once()
            ->andReturn(Mockery::mock(UserInterface::class));

        $this->creator->createWithMobileNumber(
            $loginname,
            $email,
            Mockery::mock(FactionInterface::class),
            'mobile',
            'password'
        );
    }

    public function testCreateCreatesPlayer(): void
    {
        $loginname = 'mehzomgLoL';
        $email = 'lol@example.com';
        $user_id = 42;
        $password = 'snafu';

        $user = Mockery::mock(UserInterface::class);
        $registration = Mockery::mock(UserRegistrationInterface::class);
        $faction = Mockery::mock(FactionInterface::class);

        $this->userRepository->shouldReceive('save')
            ->with($user)
            ->twice();
        $this->userRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $user->shouldReceive('getRegistration')
            ->withNoArgs()
            ->once()
            ->andReturn($registration);
        $registration->shouldReceive('setLogin')
            ->with($loginname)
            ->once()
            ->andReturnSelf();
        $registration->shouldReceive('setEmail')
            ->with($email)
            ->once()
            ->andReturnSelf();
        $registration->shouldReceive('setEmailCode')
            ->with(null)
            ->once()
            ->andReturnSelf();
        $user->shouldReceive('setFaction')
            ->with($faction)
            ->once()
            ->andReturnSelf();
        $user->shouldReceive('setUsername')
            ->with(sprintf('Siedler %d', $user_id))
            ->once()
            ->andReturnSelf();
        $registration->shouldReceive('setCreationDate')
            ->with(Mockery::type('int'))
            ->once()
            ->andReturnSelf();
        $registration->shouldReceive('setPassword')
            ->with(Mockery::on(fn(string $passwordParam): bool => password_verify($password, $passwordParam)))
            ->once()
            ->andReturnSelf();
        $user->shouldReceive('setState')
            ->with(Mockery::any())
            ->once()
            ->andReturnSelf();
        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($user_id);

        $this->playerDefaultsCreator->shouldReceive('createDefault')
            ->with($user)
            ->once();

        $this->registrationEmailSender->shouldReceive('send')
            ->with($user, Mockery::type('string'))
            ->never();

        $this->entityManager->shouldReceive('flush')
            ->withNoArgs()
            ->once();

        $this->creator->createPlayer(
            $loginname,
            $email,
            $faction,
            $password
        );
    }
}
