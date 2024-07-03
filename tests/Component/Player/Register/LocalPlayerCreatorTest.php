<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register;

use Override;
use Doctrine\ORM\EntityManagerInterface;
use Hackzilla\PasswordGenerator\Generator\PasswordGeneratorInterface;
use Mockery;
use Mockery\MockInterface;
use Stu\Module\Control\StuHashInterface;
use Stu\Orm\Entity\FactionInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

class LocalPlayerCreatorTest extends StuTestCase
{
    /** @var MockInterface&UserRepositoryInterface */
    private MockInterface $userRepository;

    /** @var MockInterface&PlayerDefaultsCreatorInterface */
    private MockInterface $playerDefaultsCreator;

    /** @var MockInterface&RegistrationEmailSenderInterface */
    private MockInterface $registrationEmailSender;

    /** @var MockInterface&SmsVerificationCodeSenderInterface */
    private MockInterface $smsVerificationCodeSender;

    /** @var MockInterface&StuHashInterface */
    private MockInterface $stuHash;

    /** @var MockInterface&PasswordGeneratorInterface */
    private MockInterface $passwordGenerator;

    /** @var MockInterface&EntityManagerInterface */
    private MockInterface $entityManager;

    private LocalPlayerCreator $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->userRepository = $this->mock(UserRepositoryInterface::class);
        $this->playerDefaultsCreator = $this->mock(PlayerDefaultsCreatorInterface::class);
        $this->registrationEmailSender = $this->mock(RegistrationEmailSenderInterface::class);
        $this->smsVerificationCodeSender = $this->mock(SmsVerificationCodeSenderInterface::class);
        $this->stuHash = $this->mock(StuHashInterface::class);
        $this->passwordGenerator = $this->mock(PasswordGeneratorInterface::class);
        $this->entityManager = $this->mock(EntityManagerInterface::class);

        $this->subject = new LocalPlayerCreator(
            $this->userRepository,
            $this->playerDefaultsCreator,
            $this->registrationEmailSender,
            $this->smsVerificationCodeSender,
            $this->stuHash,
            $this->passwordGenerator,
            $this->entityManager
        );
    }

    public function testCreatePlayerCreates(): void
    {
        $loginName = 'some-name';
        $emailAddress = 'some-email';
        $password = 'some-password';
        $userId = 666;

        $faction = $this->mock(FactionInterface::class);
        $user = $this->mock(UserInterface::class);

        $this->userRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $this->userRepository->shouldReceive('save')
            ->with($user)
            ->twice();

        $user->shouldReceive('setLogin')
            ->with($loginName)
            ->once();
        $user->shouldReceive('setEmail')
            ->with($emailAddress)
            ->once();
        $user->shouldReceive('setFaction')
            ->with($faction)
            ->once();
        $user->shouldReceive('setUsername')
            ->with(
                sprintf('Siedler %d', $userId)
            )
            ->once();
        $user->shouldReceive('setTick')
            ->with(1)
            ->once();
        $user->shouldReceive('setCreationDate')
            ->with(Mockery::type('int'))
            ->once();
        $user->shouldReceive('setPassword')
            ->with(Mockery::on(fn(string $passwordHash): bool => password_verify($password, $passwordHash)))
            ->once();
        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $this->playerDefaultsCreator->shouldReceive('createDefault')
            ->with($user)
            ->once();

        static::assertSame(
            $user,
            $this->subject->createPlayer(
                $loginName,
                $emailAddress,
                $faction,
                $password
            )
        );
    }
}
