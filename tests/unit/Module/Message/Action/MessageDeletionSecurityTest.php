<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action;

use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Message\Action\DeletePmCategory\DeletePmCategory;
use Stu\Module\Message\Action\DeletePmCategory\DeletePmCategoryRequestInterface;
use Stu\Module\Message\Action\DeletePms\DeletePms;
use Stu\Module\Message\Action\DeletePms\DeletePmsRequestInterface;
use Stu\Orm\Entity\PrivateMessage;
use Stu\Orm\Entity\PrivateMessageFolder;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;
use Stu\ProtectedMutationTestCase;

final class MessageDeletionSecurityTest extends ProtectedMutationTestCase
{
    public function testBothDeletionActionsRejectForgedRequests(): void
    {
        $this->assertRequestProtection(new DeletePms($this->mock(DeletePmsRequestInterface::class), $this->mock(PrivateMessageRepositoryInterface::class)));
        $this->assertRequestProtection(new DeletePmCategory($this->mock(DeletePmCategoryRequestInterface::class), $this->mock(PrivateMessageFolderRepositoryInterface::class), $this->mock(PrivateMessageRepositoryInterface::class)));
    }

    #[DataProvider('ownership')]
    public function testMessageDeletionPreservesRecipientCheck(bool $owned): void
    {
        $request = $this->mock(DeletePmsRequestInterface::class);
        $request->shouldReceive('getDeletionIds')->once()->andReturn([7]);
        $repository = $this->mock(PrivateMessageRepositoryInterface::class);
        $message = $this->mock(PrivateMessage::class);
        $recipient = $this->mock(User::class);
        $recipient->shouldReceive('getId')->andReturn($owned ? 101 : 102);
        $message->shouldReceive('getRecipient')->andReturn($recipient);
        $repository->shouldReceive('find')->with(7)->once()->andReturn($message);
        if ($owned) {
            $message->shouldReceive('setDeleted')->withArgs(fn (int $timestamp): bool => $timestamp > 0)->once();
            $repository->shouldReceive('save')->with($message)->once();
        }
        new DeletePms($request, $repository)->handle($this->context());
    }

    #[DataProvider('ownership')]
    public function testFolderDeletionPreservesOwnership(bool $owned): void
    {
        $request = $this->mock(DeletePmCategoryRequestInterface::class);
        $request->shouldReceive('getCategoryId')->once()->andReturn(8);
        $folders = $this->mock(PrivateMessageFolderRepositoryInterface::class);
        $messages = $this->mock(PrivateMessageRepositoryInterface::class);
        $folder = $this->mock(PrivateMessageFolder::class);
        $folder->shouldReceive('getUserId')->andReturn($owned ? 101 : 102);
        $folders->shouldReceive('find')->with(8)->once()->andReturn($folder);
        if ($owned) {
            $folder->shouldReceive('isDeleteAble')->once()->andReturn(true);
            $folder->shouldReceive('getId')->once()->andReturn(8);
            $folder->shouldReceive('setDeleted')->withArgs(fn (int $timestamp): bool => $timestamp > 0)->once();
            $messages->shouldReceive('setDeleteTimestampByFolder')->withArgs(fn (int $id, int $timestamp): bool => $id === 8 && $timestamp > 0)->once();
            $folders->shouldReceive('save')->with($folder)->once();
        }
        new DeletePmCategory($request, $folders, $messages)->handle($this->context());
    }

    public static function ownership(): iterable
    {
        yield 'own' => [true];
        yield 'foreign' => [false];
    }

    private function context(): ActionControllerContext
    {
        $context = $this->mock(ActionControllerContext::class);
        $user = $this->mock(User::class);
        $user->shouldReceive('getId')->andReturn(101);
        $context->shouldReceive('getUser')->andReturn($user);
        $information = $this->mock(InformationWrapper::class);
        $information->shouldReceive('addInformation')->zeroOrMoreTimes();
        $context->shouldReceive('getInfo')->andReturn($information);
        return $context;
    }
}
