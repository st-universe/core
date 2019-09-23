<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\Player;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Psr\Http\Message\ServerRequestInterface;
use Stu\Module\Api\Middleware\Response\JsonResponseInterface;
use Stu\Module\Api\Middleware\SessionInterface;
use Stu\Module\Communication\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Orm\Entity\PrivateMessageFolderInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;

class GetNewPrivateMessagesTest extends MockeryTestCase
{
    /**
     * @var null|MockInterface|SessionInterface
     */
    private $session;

    /**
     * @var null|MockInterface|PrivateMessageFolderRepositoryInterface
     */
    private $privateMessageFolderRepository;

    /**
     * @var null|GetNewPrivateMessages
     */
    private $handler;

    public function setUp(): void
    {
        $this->session = Mockery::mock(SessionInterface::class);
        $this->privateMessageFolderRepository = Mockery::mock(PrivateMessageFolderRepositoryInterface::class);

        $this->handler = new GetNewPrivateMessages(
            $this->session,
            $this->privateMessageFolderRepository
        );
    }

    public function testAction(): void
    {
        $userId = 666;

        $request = Mockery::mock(ServerRequestInterface::class);
        $response = Mockery::mock(JsonResponseInterface::class);
        $folder = Mockery::mock(PrivateMessageFolderInterface::class);

        $this->session->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $pmFolder = [
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_MAIN,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE,
        ];

        $folders = [];

        $folder->shouldReceive('getCategoryCountNew')
            ->withNoArgs()
            ->times(4)
            ->andReturn(0);

        foreach ($pmFolder as $folderSpecialId) {
            $this->privateMessageFolderRepository->shouldReceive('getByUserAndSpecial')
                ->with($userId, $folderSpecialId)
                ->once()
                ->andReturn($folder);

            $folders[] = ['folder_special_id' => $folderSpecialId, 'new_pm_amount' => 0];
        }

        $response->shouldReceive('withData')
            ->with($folders)
            ->once()
            ->andReturnSelf();

        $this->assertSame(
            $response,
            call_user_func($this->handler, $request, $response, [])
        );
    }
}
