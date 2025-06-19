<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register;

use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mockery\MockInterface;
use Override;
use Stu\Module\Control\StuHashInterface;
use Stu\Orm\Entity\FactionInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserRegistrationInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\Orm\Repository\UserRefererRepositoryInterface;
use Stu\StuTestCase;

class LocalPlayerCreatorTest extends StuTestCase
{
    private MockInterface&UserRepositoryInterface $userRepository;

    private MockInterface&PlayerDefaultsCreatorInterface $playerDefaultsCreator;

    private MockInterface&RegistrationEmailSenderInterface $registrationEmailSender;

    private MockInterface&SmsVerificationCodeSenderInterface $smsVerificationCodeSender;

    private MockInterface&StuHashInterface $stuHash;

    private MockInterface&EntityManagerInterface $entityManager;

    private MockInterface&UserRefererRepositoryInterface $userRefererRepository;

    private LocalPlayerCreator $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->userRepository = $this->mock(UserRepositoryInterface::class);
        $this->playerDefaultsCreator = $this->mock(PlayerDefaultsCreatorInterface::class);
        $this->registrationEmailSender = $this->mock(RegistrationEmailSenderInterface::class);
        $this->smsVerificationCodeSender = $this->mock(SmsVerificationCodeSenderInterface::class);
        $this->stuHash = $this->mock(StuHashInterface::class);
        $this->entityManager = $this->mock(EntityManagerInterface::class);
        $this->userRefererRepository = $this->mock(UserRefererRepositoryInterface::class);

        $this->subject = new LocalPlayerCreator(
            $this->userRepository,
            $this->playerDefaultsCreator,
            $this->registrationEmailSender,
            $this->smsVerificationCodeSender,
            $this->stuHash,
            $this->entityManager,
            $this->userRefererRepository
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
        $registration = $this->mock(UserRegistrationInterface::class);

        $this->userRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $this->userRepository->shouldReceive('save')
            ->with($user)
            ->once();

        $user->shouldReceive('getRegistration')
            ->withNoArgs()
            ->once()
            ->andReturn($registration);
        $registration->shouldReceive('setLogin')
            ->with($loginName)
            ->once();
        $registration->shouldReceive('setEmail')
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
        $registration->shouldReceive('setCreationDate')
            ->with(Mockery::type('int'))
            ->once();
        $registration->shouldReceive('setPassword')
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
