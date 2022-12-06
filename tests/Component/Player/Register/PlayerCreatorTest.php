<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register;

use Hackzilla\PasswordGenerator\Generator\PasswordGeneratorInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Noodlehaus\ConfigInterface;
use Stu\Component\Player\Register\Exception\EmailAddressInvalidException;
use Stu\Component\Player\Register\Exception\InvitationTokenInvalidException;
use Stu\Component\Player\Register\Exception\LoginNameInvalidException;
use Stu\Component\Player\Register\Exception\PlayerDuplicateException;
use Stu\Module\Control\StuHashInterface;
use Stu\Orm\Entity\FactionInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserInvitationInterface;
use Stu\Orm\Repository\UserInvitationRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

class PlayerCreatorTest extends MockeryTestCase
{
    //TOTEST createWithMobileNumber
    /**
     * @var null|MockInterface|UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var null|MockInterface|PlayerDefaultsCreatorInterface
     */
    private $playerDefaultsCreator;

    /**
     * @var null|MockInterface|RegistrationEmailSenderInterface
     */
    private $registrationEmailSender;

    /**
     * @var null|MockInterface|SmsVerificationCodeSenderInterface
     */
    private $smsVerificationCodeSender;

    /**
     * @var null|MockInterface|UserInvitationRepositoryInterface
     */
    private $userInvitationRepository;

    /**
     * @var null|MockInterface|StuHashInterface
     */
    private $stuHash;

    /**
     * @var null|MockInterface|ConfigInterface
     */
    private $config;

    /**
     * @var null|MockInterface|PasswordGeneratorInterface
     */
    private $passwordGenerator;

    /**
     * @var null|PlayerCreator
     */
    private $creator;

    public function setUp(): void
    {
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->playerDefaultsCreator = Mockery::mock(PlayerDefaultsCreatorInterface::class);
        $this->registrationEmailSender = Mockery::mock(RegistrationEmailSenderInterface::class);
        $this->smsVerificationCodeSender = Mockery::mock(SmsVerificationCodeSenderInterface::class);
        $this->userInvitationRepository = Mockery::mock(UserInvitationRepositoryInterface::class);
        $this->stuHash = Mockery::mock(StuHashInterface::class);
        $this->config = Mockery::mock(ConfigInterface::class);
        $this->passwordGenerator = Mockery::mock(PasswordGeneratorInterface::class);

        $this->creator = new PlayerCreator(
            $this->userRepository,
            $this->playerDefaultsCreator,
            $this->registrationEmailSender,
            $this->smsVerificationCodeSender,
            $this->userInvitationRepository,
            $this->stuHash,
            $this->config,
            $this->passwordGenerator
        );
    }

    public function testCreateThrowsErrorOnInvalidLoginName(): void
    {
        $this->expectException(LoginNameInvalidException::class);

        $this->creator->createViaToken(
            'meh',
            'lol',
            Mockery::mock(FactionInterface::class),
            'zomg'
        );
    }

    public function testCreateThrowsErrorOnInvalidEmail(): void
    {
        $this->expectException(EmailAddressInvalidException::class);

        $this->creator->createViaToken(
            'mehzomglol',
            'lol',
            Mockery::mock(FactionInterface::class),
            'zomg'
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

        $this->creator->createViaToken(
            $loginname,
            'lol@example.com',
            Mockery::mock(FactionInterface::class),
            'zomg'
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

        $this->creator->createViaToken(
            $loginname,
            $email,
            Mockery::mock(FactionInterface::class),
            'zomg'
        );
    }

    public function testCreateThrowsErrorOnNonExistingInvitation(): void
    {
        $this->expectException(InvitationTokenInvalidException::class);

        $loginname = 'mehzomglol';
        $email = 'lol@example.com';
        $token = 'some-token';

        $this->userRepository->shouldReceive('getByLogin')
            ->with($loginname)
            ->once()
            ->andReturnNull();
        $this->userRepository->shouldReceive('getByEmail')
            ->with($email)
            ->once()
            ->andReturnNull();

        $this->userInvitationRepository->shouldReceive('getByToken')
            ->with($token)
            ->once()
            ->andReturnNull();

        $this->creator->createViaToken(
            $loginname,
            $email,
            Mockery::mock(FactionInterface::class),
            $token
        );
    }

    public function testCreateThrowsErrorOnInvalidInvitation(): void
    {
        $this->expectException(InvitationTokenInvalidException::class);

        $loginname = 'mehzomglol';
        $email = 'lol@example.com';
        $token = 'some-token';
        $ttl = 666;

        $invitation = Mockery::mock(UserInvitationInterface::class);

        $this->userRepository->shouldReceive('getByLogin')
            ->with($loginname)
            ->once()
            ->andReturnNull();
        $this->userRepository->shouldReceive('getByEmail')
            ->with($email)
            ->once()
            ->andReturnNull();

        $this->userInvitationRepository->shouldReceive('getByToken')
            ->with($token)
            ->once()
            ->andReturn($invitation);

        $this->config->shouldReceive('get')
            ->with('game.invitation.ttl')
            ->once()
            ->andReturn($ttl);

        $invitation->shouldReceive('isValid')
            ->with($ttl)
            ->once()
            ->andReturnFalse();

        $this->creator->createViaToken(
            $loginname,
            $email,
            Mockery::mock(FactionInterface::class),
            $token
        );
    }

    public function testCreateCreatesPlayer(): void
    {
        $loginname = 'mehzomgLoL';
        $email = 'lol@example.com';
        $token = 'some-token';
        $ttl = 666;
        $user_id = 42;
        $generated_password = 'snafu';

        $invitation = Mockery::mock(UserInvitationInterface::class);
        $user = Mockery::mock(UserInterface::class);
        $faction = Mockery::mock(FactionInterface::class);

        $this->userRepository->shouldReceive('getByLogin')
            ->with($loginname)
            ->once()
            ->andReturnNull();
        $this->userRepository->shouldReceive('getByEmail')
            ->with($email)
            ->once()
            ->andReturnNull();
        $this->userRepository->shouldReceive('save')
            ->with($user)
            ->twice();
        $this->userRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $this->userInvitationRepository->shouldReceive('getByToken')
            ->with($token)
            ->once()
            ->andReturn($invitation);
        $this->userInvitationRepository->shouldReceive('save')
            ->with($invitation)
            ->once();

        $this->config->shouldReceive('get')
            ->with('game.invitation.ttl')
            ->once()
            ->andReturn($ttl);

        $invitation->shouldReceive('isValid')
            ->with($ttl)
            ->once()
            ->andReturnTrue();

        $user->shouldReceive('setLogin')
            ->with($loginname)
            ->once()
            ->andReturnSelf();
        $user->shouldReceive('setEmail')
            ->with($email)
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
        $user->shouldReceive('setTick')
            ->with(1)
            ->once()
            ->andReturnSelf();
        $user->shouldReceive('setCreationDate')
            ->with(Mockery::type('int'))
            ->once()
            ->andReturnSelf();
        $user->shouldReceive('setPassword')
            ->with(Mockery::on(function (string $password) use ($generated_password): bool {
                return password_verify($generated_password, $password);
            }))
            ->once()
            ->andReturnSelf();
        $user->shouldReceive('getId')
            ->withNoArgs()
            ->twice()
            ->andReturn($user_id);

        $this->passwordGenerator->shouldReceive('generatePassword')
            ->withNoArgs()
            ->once()
            ->andReturn($generated_password);

        $invitation->shouldReceive('setInvitedUserId')
            ->with($user_id)
            ->once();

        $this->playerDefaultsCreator->shouldReceive('createDefault')
            ->with($user)
            ->once();

        $this->registrationEmailSender->shouldReceive('send')
            ->with($user, $generated_password)
            ->once();

        $this->creator->createViaToken(
            $loginname,
            $email,
            $faction,
            $token
        );
    }
}
