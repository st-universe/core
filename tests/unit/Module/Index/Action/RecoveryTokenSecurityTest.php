<?php

declare(strict_types=1);

namespace Stu\Module\Index\Action;

use Hackzilla\PasswordGenerator\Generator\PasswordGeneratorInterface;
use Noodlehaus\ConfigInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Exception\InvalidParamException;
use Stu\Lib\AccountToken;
use Stu\Lib\Mail\MailFactoryInterface;
use Stu\Lib\Mail\StuMailInterface;
use Stu\Lib\Session\PasswordHasherInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Index\Action\ResetPassword\ResetPassword;
use Stu\Module\Index\Action\ResetPassword\ResetPasswordRequestInterface;
use Stu\Module\Index\Action\SendPassword\SendPassword;
use Stu\Module\Index\Action\SendPassword\SendPasswordRequestInterface;
use Stu\Module\Index\View\ShowResetPassword\ShowResetPassword;
use Stu\Module\Index\View\ShowResetPassword\ShowResetPasswordRequestInterface;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserRegistration;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

final class RecoveryTokenSecurityTest extends StuTestCase
{
    public function testIssuedTokensAreIndependentOfAccountAndRequestTime(): void
    {
        $user = new User();
        $registration = new UserRegistration($user)->setEmail('test@example.test')->setLogin('testlogin');
        $user->setRegistration($registration);
        $request = $this->mock(SendPasswordRequestInterface::class);
        $request->shouldReceive('getEmailAddress')->twice()->andReturn('test@example.test');
        $users = $this->mock(UserRepositoryInterface::class);
        $users->shouldReceive('getByEmail')->twice()->with('test@example.test')->andReturn($user);
        $users->shouldReceive('save')->twice()->with($user);
        $config = $this->mock(ConfigInterface::class);
        $config->shouldReceive('get')->with('game.base_url')->andReturn('https://example.test');
        $mail = $this->mock(StuMailInterface::class);
        foreach (['withDefaultSender', 'addTo', 'setSubject'] as $method) {
            $mail->shouldReceive($method)->twice()->andReturnSelf();
        }
        $mail->shouldReceive('setBody')->twice()->withArgs(fn ($body) => str_contains($body, $registration->getPasswordToken()))->andReturnSelf();
        $mail->shouldReceive('send')->twice();
        $factory = $this->mock(MailFactoryInterface::class);
        $factory->shouldReceive('createStuMail')->twice()->andReturn($mail);
        $context = $this->mock(ActionControllerContext::class);
        $context->shouldReceive('setView')->twice();
        $context->shouldReceive('getInfo->addInformation')->twice();
        $subject = new SendPassword($request, $users, $factory, $config);
        $subject->handle($context);
        $first = $registration->getPasswordToken();
        $subject->handle($context);
        $second = $registration->getPasswordToken();
        self::assertNotSame($first, $second);
        self::assertTrue(AccountToken::isValid($first, AccountToken::PASSWORD_RESET));
        self::assertTrue(AccountToken::isValid($second, AccountToken::PASSWORD_RESET));
        self::assertNotSame(sha1(time() . 'testlogin'), $second);
    }

    #[DataProvider('invalidTokens')]
    public function testInvalidTokensCannotOpenFormOrChangePassword(string $token, bool $view): void
    {
        $users = $this->mock(UserRepositoryInterface::class);
        $users->shouldNotReceive('getByResetToken', 'save');
        $this->expectException(InvalidParamException::class);
        if ($view) {
            $request = $this->mock(ShowResetPasswordRequestInterface::class);
            $request->shouldReceive('getToken')->once()->andReturn($token);
            new ShowResetPassword($request, $users)->handle($this->mock(ViewControllerContext::class));
        } else {
            $request = $this->mock(ResetPasswordRequestInterface::class);
            $request->shouldReceive('getToken')->once()->andReturn($token);
            new ResetPassword($request, $this->mock(MailFactoryInterface::class), $this->mock(ConfigInterface::class), $users, $this->mock(PasswordGeneratorInterface::class), $this->mock(PasswordHasherInterface::class))->handle($this->mock(ActionControllerContext::class));
        }
    }

    public static function invalidTokens(): iterable
    {
        foreach (['', sha1(time() . 'testlogin'), 'password-reset.' . (time() - 1) . '.' . str_repeat('a', 64),
            'account-deletion.' . (time() + 1000) . '.' . str_repeat('a', 64),
            'password-reset.' . (time() + 1000) . '.short'] as $token) {
            yield [$token, true];
            yield [$token, false];
        }
    }

    public function testExpiredOrWrongPurposeTokensAreNotValid(): void
    {
        self::assertFalse(AccountToken::isValid('password-reset.' . (time() - 1) . '.' . str_repeat('a', 64), AccountToken::PASSWORD_RESET));
        self::assertFalse(AccountToken::isValid(AccountToken::create(AccountToken::PASSWORD_RESET), AccountToken::ACCOUNT_DELETION));
    }
}
