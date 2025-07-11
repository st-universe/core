<?php

declare(strict_types=1);

namespace Stu\Module\Message\Lib;

use Mockery\MockInterface;
use Override;
use Stu\Orm\Entity\PrivateMessageFolder;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;
use Stu\StuTestCase;

class PrivateMessageUiFactoryTest extends StuTestCase
{
    private MockInterface&PrivateMessageRepositoryInterface $privateMessageRepository;

    private PrivateMessageUiFactory $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->privateMessageRepository = $this->mock(PrivateMessageRepositoryInterface::class);

        $this->subject = new PrivateMessageUiFactory(
            $this->privateMessageRepository
        );
    }

    public function testCreatePrivateMessageFolderItem(): void
    {
        static::assertInstanceOf(
            PrivateMessageFolderItem::class,
            $this->subject->createPrivateMessageFolderItem(
                $this->mock(PrivateMessageFolder::class)
            )
        );
    }
}
