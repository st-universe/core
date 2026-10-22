<?php

declare(strict_types=1);

namespace Stu\Module\Index\Action;

use Noodlehaus\ConfigInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Player\Deletion\Confirmation\RequestDeletionConfirmation;
use Stu\Lib\AccountToken;
use Stu\Lib\Mail\MailFactoryInterface;
use Stu\Lib\Mail\StuMailInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Index\Action\DeletionConfirmation\DeletionConfirmation;
use Stu\Module\Index\Action\DeletionConfirmation\DeletionConfirmationRequestInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserRegistration;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

final class DeletionTokenSecurityTest extends StuTestCase
{
    public function testDeletionRequestsIssueIndependentTokensAndMailTheirExpiry(): void
    {
        $user = new User();
        $registration = new UserRegistration($user)->setEmail('test@example.test');
        $user->setRegistration($registration);
        $users = $this->mock(UserRepositoryInterface::class);
        $users->shouldReceive('save')->twice()->with($user);
        $config = $this->mock(ConfigInterface::class);
        $config->shouldReceive('get')->twice()->with('game.base_url')->andReturn('https://example.test');
        $mail = $this->mock(StuMailInterface::class);
        foreach (['withDefaultSender', 'addTo', 'setSubject'] as $method) {
            $mail->shouldReceive($method)->twice()->andReturnSelf();
        }
        $mail->shouldReceive('setBody')->twice()->withArgs(fn ($body) => str_contains($body, $registration->getDeletionToken()) && str_contains($body, 'eine Stunde'))->andReturnSelf();
        $mail->shouldReceive('send')->twice();
        $factory = $this->mock(MailFactoryInterface::class);
        $factory->shouldReceive('createStuMail')->twice()->andReturn($mail);
        $subject = new RequestDeletionConfirmation($users, $factory, $config);
        $subject->request($user);
        $first = $registration->getDeletionToken();
        $subject->request($user);
        $second = $registration->getDeletionToken();
        self::assertNotSame($first, $second);
        self::assertTrue(AccountToken::isValid($first, AccountToken::ACCOUNT_DELETION));
        self::assertTrue(AccountToken::isValid($second, AccountToken::ACCOUNT_DELETION));
        self::assertSame(UserConstants::DELETION_REQUESTED, $registration->getDeletionMark());
        self::assertNotSame(sha1(time() . $registration->getCreationDate()), $second);
    }

    #[DataProvider('invalidTokens')]
    public function testInvalidTokensNeverReachTheAccountLookup(string $token): void
    {
        $request = $this->mock(DeletionConfirmationRequestInterface::class);
        $request->shouldReceive('getToken')->once()->andReturn($token);
        $users = $this->mock(UserRepositoryInterface::class);
        $users->shouldNotReceive('getByDeletionToken', 'save');
        new DeletionConfirmation($request, $users)->handle($this->mock(ActionControllerContext::class));
    }

    public static function invalidTokens(): iterable
    {
        yield [''];
        yield [sha1(time() . 123456789)];
        yield ['account-deletion.' . (time() - 1) . '.' . str_repeat('a', 64)];
        yield [AccountToken::create(AccountToken::PASSWORD_RESET)];
        yield ['account-deletion.' . (time() + 1000) . '.short'];
    }

    public function testValidTokenConfirmsPendingDeletionAndCannotBeReused(): void
    {
        $token = AccountToken::create(AccountToken::ACCOUNT_DELETION);
        $user = new User();
        $registration = new UserRegistration($user)->setDeletionMark(UserConstants::DELETION_REQUESTED)->setDeletionToken($token);
        $user->setRegistration($registration);
        $request = $this->mock(DeletionConfirmationRequestInterface::class);
        $request->shouldReceive('getToken')->twice()->andReturn($token);
        $users = $this->mock(UserRepositoryInterface::class);
        $users->shouldReceive('getByDeletionToken')->twice()->with($token)->andReturnUsing(fn () => $registration->getDeletionToken() === $token ? $user : null);
        $users->shouldReceive('save')->once()->with($user);
        $context = $this->mock(ActionControllerContext::class);
        $context->shouldReceive('getInfo->addInformation')->once();
        $subject = new DeletionConfirmation($request, $users);
        $subject->handle($context);
        self::assertSame(UserConstants::DELETION_CONFIRMED, $registration->getDeletionMark());
        self::assertNull($registration->getDeletionToken());
        $subject->handle($context);
    }

    public function testValidTokenCannotConfirmAnAccountWithoutPendingDeletion(): void
    {
        $token = AccountToken::create(AccountToken::ACCOUNT_DELETION);
        $user = new User();
        $registration = new UserRegistration($user)->setDeletionToken($token);
        $user->setRegistration($registration);
        $request = $this->mock(DeletionConfirmationRequestInterface::class);
        $request->shouldReceive('getToken')->once()->andReturn($token);
        $users = $this->mock(UserRepositoryInterface::class);
        $users->shouldReceive('getByDeletionToken')->once()->with($token)->andReturn($user);
        $users->shouldNotReceive('save');
        new DeletionConfirmation($request, $users)->handle($this->mock(ActionControllerContext::class));
        self::assertSame(0, $registration->getDeletionMark());
        self::assertSame($token, $registration->getDeletionToken());
    }
}
