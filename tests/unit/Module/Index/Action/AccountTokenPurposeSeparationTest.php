<?php

declare(strict_types=1);

namespace Stu\Module\Index\Action;

use Noodlehaus\ConfigInterface;
use Stu\Component\Player\Deletion\Confirmation\RequestDeletionConfirmation;
use Stu\Lib\AccountToken;
use Stu\Lib\Mail\MailFactoryInterface;
use Stu\Lib\Mail\StuMailInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Index\Action\DeletionConfirmation\DeletionConfirmation;
use Stu\Module\Index\Action\DeletionConfirmation\DeletionConfirmationRequestInterface;
use Stu\Module\Index\Action\SendPassword\SendPassword;
use Stu\Module\Index\Action\SendPassword\SendPasswordRequestInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserRegistration;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

final class AccountTokenPurposeSeparationTest extends StuTestCase
{
    public function testPasswordRecoveryPreservesPendingDeletionCredential(): void
    {
        $user = $this->user();
        $registration = $user->getRegistration();
        $deletionToken = AccountToken::create(AccountToken::ACCOUNT_DELETION);
        $registration->setDeletionMark(UserConstants::DELETION_REQUESTED)->setDeletionToken($deletionToken);
        $request = $this->mock(SendPasswordRequestInterface::class);
        $request->shouldReceive('getEmailAddress')->once()->andReturn('owner@example.test');
        $users = $this->mock(UserRepositoryInterface::class);
        $users->shouldReceive('getByEmail')->with('owner@example.test')->once()->andReturn($user);
        $users->shouldReceive('save')->with($user)->once();
        $context = $this->mock(ActionControllerContext::class);
        $context->shouldReceive('setView')->once();
        $context->shouldReceive('getInfo->addInformation')->once();
        new SendPassword($request, $users, $this->mailFactory(), $this->config())->handle($context);
        self::assertSame($deletionToken, $registration->getDeletionToken());
        self::assertSame(UserConstants::DELETION_REQUESTED, $registration->getDeletionMark());
        self::assertTrue(AccountToken::isValid($registration->getPasswordToken(), AccountToken::PASSWORD_RESET));
    }

    public function testDeletionRequestPreservesExistingPasswordRecoveryCredential(): void
    {
        $user = $this->user();
        $registration = $user->getRegistration();
        $recoveryToken = AccountToken::create(AccountToken::PASSWORD_RESET);
        $registration->setPasswordToken($recoveryToken);
        $users = $this->mock(UserRepositoryInterface::class);
        $users->shouldReceive('save')->with($user)->once();
        new RequestDeletionConfirmation($users, $this->mailFactory(), $this->config())->request($user);
        self::assertSame($recoveryToken, $registration->getPasswordToken());
        self::assertSame(UserConstants::DELETION_REQUESTED, $registration->getDeletionMark());
        self::assertNotNull($registration->getDeletionToken());
        self::assertTrue(AccountToken::isValid($registration->getDeletionToken(), AccountToken::ACCOUNT_DELETION));
    }

    public function testDeletionConfirmationConsumesOnlyItsOwnCredential(): void
    {
        $user = $this->user();
        $registration = $user->getRegistration();
        $recoveryToken = AccountToken::create(AccountToken::PASSWORD_RESET);
        $deletionToken = AccountToken::create(AccountToken::ACCOUNT_DELETION);
        $registration->setPasswordToken($recoveryToken)->setDeletionToken($deletionToken)->setDeletionMark(UserConstants::DELETION_REQUESTED);
        $request = $this->mock(DeletionConfirmationRequestInterface::class);
        $request->shouldReceive('getToken')->once()->andReturn($deletionToken);
        $users = $this->mock(UserRepositoryInterface::class);
        $users->shouldReceive('getByDeletionToken')->with($deletionToken)->once()->andReturn($user);
        $users->shouldNotReceive('getByResetToken');
        $users->shouldReceive('save')->with($user)->once();
        $context = $this->mock(ActionControllerContext::class);
        $context->shouldReceive('getInfo->addInformation')->once();
        new DeletionConfirmation($request, $users)->handle($context);
        self::assertNull($registration->getDeletionToken());
        self::assertSame($recoveryToken, $registration->getPasswordToken());
        self::assertSame(UserConstants::DELETION_CONFIRMED, $registration->getDeletionMark());
    }

    public function testPasswordRecoveryCredentialIsRejectedBeforeDeletionLookup(): void
    {
        $request = $this->mock(DeletionConfirmationRequestInterface::class);
        $request->shouldReceive('getToken')->once()->andReturn(AccountToken::create(AccountToken::PASSWORD_RESET));
        $users = $this->mock(UserRepositoryInterface::class);
        $users->shouldNotReceive('getByResetToken', 'getByDeletionToken', 'save');
        new DeletionConfirmation($request, $users)->handle($this->mock(ActionControllerContext::class));
    }

    private function user(): User
    {
        $user = new User();
        $user->setRegistration(new UserRegistration($user)->setEmail('owner@example.test')->setLogin('ownerlogin'));
        return $user;
    }

    private function config(): ConfigInterface
    {
        $config = $this->mock(ConfigInterface::class);
        $config->shouldReceive('get')->with('game.base_url')->andReturn('https://example.test');
        return $config;
    }

    private function mailFactory(): MailFactoryInterface
    {
        $mail = $this->mock(StuMailInterface::class);
        foreach (['withDefaultSender', 'setSubject', 'setBody'] as $method) {
            $mail->shouldReceive($method)->once()->andReturnSelf();
        }
        $mail->shouldReceive('addTo')->with('owner@example.test')->once()->andReturnSelf();
        $mail->shouldReceive('send')->once();
        $factory = $this->mock(MailFactoryInterface::class);
        $factory->shouldReceive('createStuMail')->once()->andReturn($mail);
        return $factory;
    }
}
