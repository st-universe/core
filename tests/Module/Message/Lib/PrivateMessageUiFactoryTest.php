<?php

declare(strict_types=1);

namespace Stu\Module\Message\Lib;

use Override;
use Mockery\MockInterface;
use Stu\Orm\Entity\PrivateMessageFolderInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;
use Stu\StuTestCase;

class PrivateMessageUiFactoryTest extends StuTestCase
{
    /** @var MockInterface&PrivateMessageRepositoryInterface */
    private MockInterface $privateMessageRepository;

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
                $this->mock(PrivateMessageFolderInterface::class)
            )
        );
    }
}
