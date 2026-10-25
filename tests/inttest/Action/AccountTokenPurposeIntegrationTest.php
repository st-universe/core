<?php

declare(strict_types=1);

namespace Stu\Action;

use Doctrine\ORM\EntityManagerInterface;
use Hackzilla\PasswordGenerator\Generator\PasswordGeneratorInterface;
use Noodlehaus\ConfigInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\ActionTestCase;
use Stu\Component\Player\Deletion\Confirmation\RequestDeletionConfirmation;
use Stu\Lib\AccountToken;
use Stu\Lib\Mail\MailFactoryInterface;
use Stu\Lib\Mail\StuMailInterface;
use Stu\Lib\Session\PasswordHasherInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Index\Action\DeletionConfirmation\DeletionConfirmation;
use Stu\Module\Index\Action\DeletionConfirmation\DeletionConfirmationRequestInterface;
use Stu\Module\Index\Action\ResetPassword\ResetPassword;
use Stu\Module\Index\Action\ResetPassword\ResetPasswordRequestInterface;
use Stu\Module\Index\Action\SendPassword\SendPassword;
use Stu\Module\Index\Action\SendPassword\SendPasswordRequestInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Repository\UserRepositoryInterface;

final class AccountTokenPurposeIntegrationTest extends ActionTestCase
{
    public function testRecoveryAndDeletionUseIndependentRepositoryLookups(): void
    {
        $dic = $this->getContainer();
        $users = $dic->get(UserRepositoryInterface::class);
        $user = $users->find(101);
        $registration = $user->getRegistration();
        self::assertNull($registration->getDeletionToken());
        $recoveryToken = AccountToken::create(AccountToken::PASSWORD_RESET);
        $deletionToken = AccountToken::create(AccountToken::ACCOUNT_DELETION);
        $registration->setPasswordToken($recoveryToken)->setDeletionToken($deletionToken);
        $dic->get(EntityManagerInterface::class)->flush();
        self::assertSame($user, $users->getByResetToken($recoveryToken));
        self::assertNull($users->getByDeletionToken($recoveryToken));
        self::assertSame($user, $users->getByDeletionToken($deletionToken));
        self::assertNull($users->getByResetToken($deletionToken));
        self::assertNull($users->getByDeletionToken(''));
        self::assertNull($users->getByDeletionToken(AccountToken::create(AccountToken::ACCOUNT_DELETION)));
    }

    public function testRecoveryForPendingDeletionPreservesBothAuthorizedWorkflows(): void
    {
        $dic = $this->getContainer();
        $em = $dic->get(EntityManagerInterface::class);
        $users = $dic->get(UserRepositoryInterface::class);
        $user = $users->find(101);
        $registration = $user->getRegistration();
        $registration->setEmail('owner@example.test');
        $em->flush();
        $mail = $this->mailFactory();
        $config = $this->config();
        new RequestDeletionConfirmation($users, $mail, $config)->request($user);
        $em->flush();
        $em->refresh($registration);
        $deletionToken = $registration->getDeletionToken();
        self::assertNotNull($deletionToken);
        $sendRequest = $this->mock(SendPasswordRequestInterface::class);
        $sendRequest->shouldReceive('getEmailAddress')->once()->andReturn('owner@example.test');
        new SendPassword($sendRequest, $users, $mail, $config)->handle($this->context());
        $em->flush();
        $em->refresh($registration);
        $recoveryToken = $registration->getPasswordToken();
        self::assertSame($deletionToken, $registration->getDeletionToken());
        self::assertSame(UserConstants::DELETION_REQUESTED, $registration->getDeletionMark());
        self::assertTrue(AccountToken::isValid($recoveryToken, AccountToken::PASSWORD_RESET));
        $originalPassword = $registration->getPassword();
        $this->confirmDeletion($recoveryToken);
        $em->flush();
        $em->refresh($registration);
        self::assertSame(UserConstants::DELETION_REQUESTED, $registration->getDeletionMark());
        self::assertSame($originalPassword, $registration->getPassword());
        self::assertSame($deletionToken, $registration->getDeletionToken());
        $resetRequest = $this->mock(ResetPasswordRequestInterface::class);
        $resetRequest->shouldReceive('getToken')->once()->andReturn($recoveryToken);
        $generator = $this->mock(PasswordGeneratorInterface::class);
        $generator->shouldReceive('generatePassword')->once()->andReturn('Neues-Testpasswort-123!');
        new ResetPassword($resetRequest, $mail, $config, $users, $generator, $dic->get(PasswordHasherInterface::class))->handle($this->context());
        $em->flush();
        $em->refresh($registration);
        self::assertTrue(password_verify('Neues-Testpasswort-123!', $registration->getPassword()));
        self::assertSame('', $registration->getPasswordToken());
        self::assertSame($deletionToken, $registration->getDeletionToken());
        $this->confirmDeletion($deletionToken);
        $em->flush();
        $em->refresh($registration);
        self::assertSame(UserConstants::DELETION_CONFIRMED, $registration->getDeletionMark());
        self::assertNull($registration->getDeletionToken());
        self::assertNull($users->getByDeletionToken($deletionToken));
        $this->confirmDeletion($deletionToken);
        $em->flush();
        $em->refresh($registration);
        self::assertSame(UserConstants::DELETION_CONFIRMED, $registration->getDeletionMark());
    }

    #[DataProvider('invalidCredentials')]
    public function testInvalidDeletionCredentialsPreservePendingState(string $kind): void
    {
        $dic = $this->getContainer();
        $em = $dic->get(EntityManagerInterface::class);
        $user = $dic->get(UserRepositoryInterface::class)->find(101);
        $registration = $user->getRegistration();
        $deletionToken = $kind === 'expired' ? 'account-deletion.' . (time() - 1) . '.' . str_repeat('a', 64) : AccountToken::create(AccountToken::ACCOUNT_DELETION);
        $registration->setDeletionMark(UserConstants::DELETION_REQUESTED)->setDeletionToken($deletionToken);
        $em->flush();
        $token = match ($kind) {
            'expired' => $deletionToken,
            'missing' => '',
            'wrong' => AccountToken::create(AccountToken::ACCOUNT_DELETION),
            'recovery' => AccountToken::create(AccountToken::PASSWORD_RESET)
        };
        $this->confirmDeletion($token);
        $em->flush();
        $em->refresh($registration);
        self::assertSame(UserConstants::DELETION_REQUESTED, $registration->getDeletionMark());
        self::assertSame($deletionToken, $registration->getDeletionToken());
    }

    public static function invalidCredentials(): iterable
    {
        foreach (['expired', 'missing', 'wrong', 'recovery'] as $kind) {
            yield [$kind];
        }
    }

    private function confirmDeletion(string $token): void
    {
        $request = $this->mock(DeletionConfirmationRequestInterface::class);
        $request->shouldReceive('getToken')->once()->andReturn($token);
        new DeletionConfirmation($request, $this->getContainer()->get(UserRepositoryInterface::class))->handle($this->context());
    }

    private function context(): ActionControllerContext
    {
        $context = $this->mock(ActionControllerContext::class);
        $context->shouldReceive('setView')->zeroOrMoreTimes();
        $context->shouldReceive('getInfo->addInformation')->zeroOrMoreTimes();
        return $context;
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
            $mail->shouldReceive($method)->zeroOrMoreTimes()->andReturnSelf();
        }
        $mail->shouldReceive('addTo')->with('owner@example.test')->zeroOrMoreTimes()->andReturnSelf();
        $mail->shouldReceive('send')->zeroOrMoreTimes();
        $factory = $this->mock(MailFactoryInterface::class);
        $factory->shouldReceive('createStuMail')->zeroOrMoreTimes()->andReturn($mail);
        return $factory;
    }
}
