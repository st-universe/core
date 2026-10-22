<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action;

use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use request;
use Stu\Component\Alliance\Enum\AllianceJobPermissionEnum;
use Stu\Exception\AccessViolationException;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Alliance\Action\AddBoard\AddBoard;
use Stu\Module\Alliance\Action\AddBoard\AddBoardRequestInterface;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Module\Colony\Action\DeleteBuildPlan\DeleteBuildPlan;
use Stu\Module\Colony\Lib\BuildPlanDeleterInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Message\Action\EditPmCategory\EditPmCategory;
use Stu\Module\Message\Action\EditPmCategory\EditPmCategoryRequestInterface;
use Stu\Module\Message\Action\MovePm\MovePm;
use Stu\Module\Message\Action\MovePm\MovePmRequestInterface;
use Stu\Module\Message\Action\SortPmCategories\SortPmCategories;
use Stu\Module\Message\Action\SortPmCategories\SortPmCategoriesRequestInterface;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceBoard;
use Stu\Orm\Entity\PrivateMessage;
use Stu\Orm\Entity\PrivateMessageFolder;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AllianceBoardRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
use Stu\ProtectedMutationTestCase;

final class MessageAndForumMutationSecurityTest extends ProtectedMutationTestCase
{
    #[DataProvider('actions')]
    public function testEveryEntryPointRejectsForgedRequests(string $class): void
    {
        $arguments = [];
        foreach (new ReflectionClass($class)->getConstructor()->getParameters() as $parameter) {
            $arguments[] = $this->mock($parameter->getType()->getName());
        }
        $this->assertRequestProtection(new $class(...$arguments));
    }

    public static function actions(): iterable
    {
        foreach ([MovePm::class, EditPmCategory::class, SortPmCategories::class, DeleteBuildPlan::class, AddBoard::class] as $class) {
            yield [$class];
        }
    }

    #[DataProvider('ownership')]
    public function testMovingMessagesPreservesDestinationOwnership(bool $owned): void
    {
        $request = $this->mock(MovePmRequestInterface::class);
        $request->shouldReceive('getPmId')->once()->andReturn(7);
        $request->shouldReceive('getDestinationCategoryId')->once()->andReturn(8);
        $messages = $this->mock(PrivateMessageRepositoryInterface::class);
        $folders = $this->mock(PrivateMessageFolderRepositoryInterface::class);
        $message = $this->mock(PrivateMessage::class);
        $message->shouldReceive('getRecipientId')->once()->andReturn(101);
        $origin = $this->mock(PrivateMessageFolder::class);
        $origin->shouldReceive('isPMOutDir')->once()->andReturn(false);
        $message->shouldReceive('getCategory')->once()->andReturn($origin);
        $destination = $this->mock(PrivateMessageFolder::class);
        $destination->shouldReceive('getUserId')->once()->andReturn($owned ? 101 : 102);
        $folders->shouldReceive('find')->with(8)->once()->andReturn($destination);
        $messages->shouldReceive('find')->with(7)->once()->andReturn($message);
        if ($owned) {
            $message->shouldReceive('setCategory')->with($destination)->once();
            $messages->shouldReceive('save')->with($message)->once();
        }
        new MovePm($request, $folders, $messages)->handle($this->context());
    }

    #[DataProvider('ownership')]
    public function testRenamingFoldersPreservesOwnership(bool $owned): void
    {
        $request = $this->mock(EditPmCategoryRequestInterface::class);
        $request->shouldReceive('getName')->once()->andReturn('Neuer Ordnername');
        $request->shouldReceive('getCategoryId')->once()->andReturn(8);
        $folders = $this->mock(PrivateMessageFolderRepositoryInterface::class);
        $folder = $this->mock(PrivateMessageFolder::class);
        $folder->shouldReceive('getUserId')->once()->andReturn($owned ? 101 : 102);
        $folders->shouldReceive('find')->with(8)->once()->andReturn($folder);
        if ($owned) {
            $folder->shouldReceive('setDescription')->with('Neuer Ordnername')->once();
            $folders->shouldReceive('save')->with($folder)->once();
        } else {
            $this->expectException(AccessViolationException::class);
        }
        new EditPmCategory($request, $folders)->handle($this->context());
    }

    #[DataProvider('ownership')]
    public function testSortingFoldersPreservesOwnership(bool $owned): void
    {
        $request = $this->mock(SortPmCategoriesRequestInterface::class);
        $request->shouldReceive('getCategoryIds')->once()->andReturn([8]);
        $folders = $this->mock(PrivateMessageFolderRepositoryInterface::class);
        $folder = $this->mock(PrivateMessageFolder::class);
        $folder->shouldReceive('getUserId')->once()->andReturn($owned ? 101 : 102);
        $folders->shouldReceive('find')->with(8)->once()->andReturn($folder);
        if ($owned) {
            $folder->shouldReceive('setSort')->with(0)->once();
            $folders->shouldReceive('save')->with($folder)->once();
        } else {
            $this->expectException(AccessViolationException::class);
        }
        new SortPmCategories($request, $folders)->handle($this->context());
    }

    #[DataProvider('ownership')]
    public function testBuildplanDeletionPreservesOwnership(bool $owned): void
    {
        request::setMockVars(['planid' => 7]);
        $plans = $this->mock(SpacecraftBuildplanRepositoryInterface::class);
        $deleter = $this->mock(BuildPlanDeleterInterface::class);
        $plan = $this->mock(SpacecraftBuildplan::class);
        $plan->shouldReceive('getUserId')->once()->andReturn($owned ? 101 : 102);
        $plans->shouldReceive('find')->with(7)->once()->andReturn($plan);
        if ($owned) {
            $deleter->shouldReceive('isDeletable')->with($plan)->once()->andReturn(true);
            $deleter->shouldReceive('delete')->with($plan)->once();
        }
        new DeleteBuildPlan($deleter, $plans)->handle($this->context());
    }

    #[DataProvider('ownership')]
    public function testBoardCreationStillRequiresAlliancePermission(bool $allowed): void
    {
        $request = $this->mock(AddBoardRequestInterface::class);
        $boards = $this->mock(AllianceBoardRepositoryInterface::class);
        $permissions = $this->mock(AllianceJobManagerInterface::class);
        $user = $this->mock(User::class);
        $alliance = $this->mock(Alliance::class);
        $user->shouldReceive('getAlliance')->once()->andReturn($alliance);
        $permissions->shouldReceive('hasUserPermission')->with($user, $alliance, AllianceJobPermissionEnum::EDIT_ALLIANCE)->once()->andReturn($allowed);
        if ($allowed) {
            $request->shouldReceive('getBoardName')->once()->andReturn('Neues Forum');
            $board = $this->mock(AllianceBoard::class);
            $boards->shouldReceive('prototype')->once()->andReturn($board);
            $board->shouldReceive('setAlliance')->with($alliance)->once();
            $board->shouldReceive('setName')->with('Neues Forum')->once();
            $boards->shouldReceive('save')->with($board)->once();
        } else {
            $this->expectException(AccessViolationException::class);
        }
        new AddBoard($request, $boards, $permissions)->handle($this->context($user));
    }

    public static function ownership(): iterable
    {
        yield 'own' => [true];
        yield 'foreign' => [false];
    }

    private function context(?User $user = null): ActionControllerContext
    {
        $context = $this->mock(ActionControllerContext::class);
        if ($user === null) {
            $user = $this->mock(User::class);
            $user->shouldReceive('getId')->andReturn(101);
        }
        $context->shouldReceive('getUser')->andReturn($user);
        $context->shouldReceive('setView')->zeroOrMoreTimes();
        $context->shouldReceive('setTemplateVar')->zeroOrMoreTimes();
        $information = $this->mock(InformationWrapper::class);
        $information->shouldReceive('addInformation')->zeroOrMoreTimes();
        $context->shouldReceive('getInfo')->andReturn($information);
        return $context;
    }
}
