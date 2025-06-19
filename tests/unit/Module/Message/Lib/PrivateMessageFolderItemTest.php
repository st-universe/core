<?php

declare(strict_types=1);

namespace Stu\Module\Message\Lib;

use Mockery\MockInterface;
use Override;
use Stu\Orm\Entity\PrivateMessageFolderInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;
use Stu\StuTestCase;

class PrivateMessageFolderItemTest extends StuTestCase
{
    private MockInterface&PrivateMessageRepositoryInterface $privateMessageRepository;

    private MockInterface&PrivateMessageFolderInterface $privateMessageFolder;

    private PrivateMessageFolderItem $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->privateMessageRepository = $this->mock(PrivateMessageRepositoryInterface::class);
        $this->privateMessageFolder = $this->mock(PrivateMessageFolderInterface::class);

        $this->subject = new PrivateMessageFolderItem(
            $this->privateMessageRepository,
            $this->privateMessageFolder
        );
    }

    public function testGetIdReturnsValue(): void
    {
        $value = 666;

        $this->privateMessageFolder->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($value);

        static::assertSame(
            $value,
            $this->subject->getId()
        );
    }

    public function testGetDescriptionReturnsValue(): void
    {
        $value = 'some-value';

        $this->privateMessageFolder->shouldReceive('getDescription')
            ->withNoArgs()
            ->once()
            ->andReturn($value);

        static::assertSame(
            $value,
            $this->subject->getDescription()
        );
    }

    public function testIsDropableReturnsValue(): void
    {
        $this->privateMessageFolder->shouldReceive('isDropable')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        static::assertTrue(
            $this->subject->isDropable()
        );
    }

    public function testGetCategoryCountReturnsValue(): void
    {
        $value = 666;

        $this->privateMessageRepository->shouldReceive('getAmountByFolder')
            ->with($this->privateMessageFolder)
            ->once()
            ->andReturn($value);

        static::assertSame(
            $value,
            $this->subject->getCategoryCount()
        );
    }

    public function testGetCategoryCountNewReturnsValue(): void
    {
        $value = 666;

        $this->privateMessageRepository->shouldReceive('getNewAmountByFolder')
            ->with($this->privateMessageFolder)
            ->once()
            ->andReturn($value);

        static::assertSame(
            $value,
            $this->subject->getCategoryCountNew()
        );
    }
}
