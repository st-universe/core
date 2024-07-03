<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register;

use Override;
use Doctrine\ORM\EntityManagerInterface;
use Hackzilla\PasswordGenerator\Generator\PasswordGeneratorInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Stu\Component\Player\Register\Exception\EmailAddressInvalidException;
use Stu\Component\Player\Register\Exception\LoginNameInvalidException;
use Stu\Component\Player\Register\Exception\PlayerDuplicateException;
use Stu\Module\Control\StuHashInterface;
use Stu\Orm\Entity\FactionInterface;
use Stu\Orm\Entity\UserInterface;
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
     * @var null|MockInterface|StuHashInterface
     */
    private $stuHash;

    /**
     * @var null|MockInterface|PasswordGeneratorInterface
     */
    private $passwordGenerator;

    /**
     * @var null|MockInterface|EntityManagerInterface
     */
    private $entityManager;

    private PlayerCreatorInterface $creator;

    #[Override]
    public function setUp(): void
    {
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->playerDefaultsCreator = Mockery::mock(PlayerDefaultsCreatorInterface::class);
        $this->registrationEmailSender = Mockery::mock(RegistrationEmailSenderInterface::class);
        $this->smsVerificationCodeSender = Mockery::mock(SmsVerificationCodeSenderInterface::class);
        $this->stuHash = Mockery::mock(StuHashInterface::class);
        $this->passwordGenerator = Mockery::mock(PasswordGeneratorInterface::class);
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);

        $this->creator = new PlayerCreator(
            $this->userRepository,
            $this->playerDefaultsCreator,
            $this->registrationEmailSender,
            $this->smsVerificationCodeSender,
            $this->stuHash,
            $this->passwordGenerator,
            $this->entityManager
        );
    }

    public function testCreateThrowsErrorOnInvalidLoginName(): void
    {
        $this->expectException(LoginNameInvalidException::class);

        $this->creator->createWithMobileNumber(
            'meh',
            'lol',
            Mockery::mock(FactionInterface::class),
            'mobile'
        );
    }

    public function testCreateThrowsErrorOnInvalidEmail(): void
    {
        $this->expectException(EmailAddressInvalidException::class);

        $this->creator->createWithMobileNumber(
            'mehzomglol',
            'lol',
            Mockery::mock(FactionInterface::class),
            'mobile'
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
            'mobile'
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
            'mobile'
        );
    }

    public function testCreateCreatesPlayer(): void
    {
        $loginname = 'mehzomgLoL';
        $email = 'lol@example.com';
        $user_id = 42;
        $password = 'snafu';

        $user = Mockery::mock(UserInterface::class);
        $faction = Mockery::mock(FactionInterface::class);

        $this->userRepository->shouldReceive('save')
            ->with($user)
            ->twice();
        $this->userRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

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
            ->with(Mockery::on(fn(string $passwordParam): bool => password_verify($password, $passwordParam)))
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
            ->with($user, $password)
            ->once();

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
