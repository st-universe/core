<?php

declare(strict_types=1);

namespace Stu\Component\Communication\Kn;

use Mockery\MockInterface;
use Override;
use Stu\Module\Template\StatusBarFactoryInterface;
use Stu\Orm\Entity\KnPost;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\KnCommentRepositoryInterface;
use Stu\StuTestCase;

class KnFactoryTest extends StuTestCase
{
    private MockInterface&KnBbCodeParser $bbcodeParser;
    private MockInterface&KnCommentRepositoryInterface $knCommentRepository;
    private MockInterface&StatusBarFactoryInterface $statusBarFactory;

    private KnFactoryInterface $factory;

    #[Override]
    public function setUp(): void
    {
        $this->bbcodeParser = $this->mock(KnBbCodeParser::class);
        $this->knCommentRepository = $this->mock(KnCommentRepositoryInterface::class);
        $this->statusBarFactory = $this->mock(StatusBarFactoryInterface::class);

        $this->factory = new KnFactory(
            $this->bbcodeParser,
            $this->knCommentRepository,
            $this->statusBarFactory
        );
    }

    public function testCreateKnItemReturnsValue(): void
    {
        $knPost = $this->mock(KnPost::class);
        $user = $this->mock(User::class);

        $this->assertInstanceOf(
            KnItem::class,
            $this->factory->createKnItem(
                $knPost,
                $user
            )
        );
    }
}
