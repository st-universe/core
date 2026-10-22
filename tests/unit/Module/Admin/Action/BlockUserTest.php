<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action;

use PHPUnit\Framework\Attributes\DataProvider;
use request;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\StuHashInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\BlockedUser;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserRegistration;
use Stu\Orm\Repository\BlockedUserRepositoryInterface;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

final class BlockUserTest extends StuTestCase
{
    public static function blockedIdentities(): array
    {
        return [
            ['test@example.test', null, null],
            ['  TEST@EXAMPLE.TEST  ', '+49 1234567890', 'canonical-mobile-hash'],
            ['test@example.test', str_repeat('a', 40), str_repeat('a', 40)]
        ];
    }

    #[DataProvider('blockedIdentities')]
    public function testBlockingRevokesActionAndRecoveryTokensBeforeAccountDeletion(string $email, ?string $mobile, ?string $mobileHash): void
    {
        request::setMockVars(['uid' => '101']);
        $user = $this->mock(User::class);
        $user->shouldReceive('getName')->andReturn('Testspieler');
        $registration = new UserRegistration($user)->setEmail($email)->setMobile($mobile)->setPasswordToken('previous-recovery-token');
        $user->shouldReceive('getRegistration')->andReturn($registration);
        $users = $this->mock(UserRepositoryInterface::class);
        $users->shouldReceive('find')->with(101)->once()->andReturn($user);
        $users->shouldReceive('save')->with($user)->once();
        $blockedUsers = $this->mock(BlockedUserRepositoryInterface::class);
        $blockedUsers->shouldReceive('find')->with(101)->once()->andReturnNull();
        $blocked = new BlockedUser();
        $blockedUsers->shouldReceive('prototype')->once()->andReturn($blocked);
        $blockedUsers->shouldReceive('save')->with($blocked)->once();
        $hasher = $this->mock(StuHashInterface::class);
        $hasher->shouldReceive('hash')->with('test@example.test')->once()->andReturn('blocked-email-hash');
        if ($mobile !== null && strlen($mobile) < 40) {
            $hasher->shouldReceive('hash')->with('00491234567890')->once()->andReturn('canonical-mobile-hash');
        }
        $tokens = $this->mock(SessionStringRepositoryInterface::class);
        $tokens->shouldReceive('truncate')->with($user)->once();
        $context = $this->mock(ActionControllerContext::class);
        $context->shouldReceive('setView')->once();
        $context->shouldReceive('getInfo->addInformationf')->once();
        $subject = new BlockUser($users, $blockedUsers, $hasher, $this->initLoggerUtil(), $tokens);
        self::assertTrue($subject->performSessionCheck());
        $subject->handle($context);
        self::assertSame(UserConstants::DELETION_CONFIRMED, $registration->getDeletionMark());
        self::assertSame('', $registration->getPasswordToken());
        self::assertSame('blocked-email-hash', $blocked->getEmailHash());
        self::assertSame($mobileHash, $blocked->getMobileHash());
    }
}
