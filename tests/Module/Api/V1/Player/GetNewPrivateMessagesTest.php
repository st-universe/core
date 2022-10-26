<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\Player;

use Mockery;
use Mockery\MockInterface;
use Stu\Module\Api\Middleware\SessionInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Orm\Entity\PrivateMessageFolderInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\StuApiV1TestCase;

class GetNewPrivateMessagesTest extends StuApiV1TestCase
{
    /**
     * @var null|MockInterface|SessionInterface
     */
    private $session;

    /**
     * @var null|MockInterface|PrivateMessageFolderRepositoryInterface
     */
    private $privateMessageFolderRepository;

    public function setUp(): void
    {
        $this->session = $this->mock(SessionInterface::class);
        $this->privateMessageFolderRepository = $this->mock(PrivateMessageFolderRepositoryInterface::class);

        $this->setUpApiHandler(
            new GetNewPrivateMessages(
                $this->session,
                $this->privateMessageFolderRepository
            )
        );
    }

    public function testAction(): void
    {
        $userId = 666;

        $folder = $this->mock(PrivateMessageFolderInterface::class);

        $this->session->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $pmFolder = [
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_MAIN,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_SYSTEM
        ];

        $folders = [];

        $folder->shouldReceive('getCategoryCountNew')
            ->withNoArgs()
            ->times(6)
            ->andReturn(0);

        foreach ($pmFolder as $folderSpecialId) {
            $this->privateMessageFolderRepository->shouldReceive('getByUserAndSpecial')
                ->with($userId, $folderSpecialId)
                ->once()
                ->andReturn($folder);

            $folders[] = ['folder_special_id' => $folderSpecialId, 'new_pm_amount' => 0];
        }

        $this->response->shouldReceive('withData')
            ->with($folders)
            ->once()
            ->andReturnSelf();

        $this->performAssertion();
    }
}
